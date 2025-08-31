<template>
  <div class="pa-4">
    <v-card>
      <v-card-title>Seri Port (Web Serial) - CH340/Arduino</v-card-title>
      <v-card-subtitle>
        - Chrome/Edge (HTTPS veya localhost) gerektirir.
        - Windows’ta CH340 sürücüsü yüklü olmalıdır.
      </v-card-subtitle>
      <v-card-text>
        <div class="d-flex flex-wrap gap-4 align-center">
          <v-btn color="primary" @click="connect" :disabled="busy">Cihaza Bağlan</v-btn>
          <v-select v-model="baudRate" :items="baudRates" label="Baud Rate" style="max-inline-size: 200px;" />
          <v-text-field v-model="tx" label="Gönder" style="max-inline-size: 300px;" @keyup.enter="send" />
          <v-btn color="secondary" @click="send" :disabled="!isConnected">Gönder</v-btn>
          <v-btn color="error" @click="disconnect" :disabled="!isConnected">Bağlantıyı Kes</v-btn>
        </div>

        <v-divider class="my-4" />

        <div class="d-flex align-center gap-6 my-4">
          <div class="status-box" :class="firstBoxClass" title="Şalter (0 → gri, 1 → yeşil)" />
          <div class="status-box" :class="secondBoxClass" title="Makine (0 → gri, 1 → yeşil)" />
          <div class="status-box" :class="thirdBoxClass" title="Sayaç Tetik (0 → gri, 1 → mavi)" />
        </div>

        <div class="mt-4 d-flex flex-column gap-2">
          <div>Şalter: <strong>{{ states.switchOn ? 'Açık (Yeşil)' : 'Kapalı (Gri)' }}</strong></div>
          <div>Makine: <strong>{{ states.machineRun ? 'Çalışıyor (Yeşil)' : 'Bekliyor (Gri)' }}</strong></div>
          <div>Tetik: <strong>{{ states.pulse ? '1 (Mavi)' : '0 (Gri)' }}</strong></div>
          <v-divider class="my-2" />
          <div>Sayaç: <v-chip color="primary" variant="flat">{{ counter }}</v-chip></div>
          <div class="text-medium-emphasis text-caption">Çerçeve formatı: 0|1|0# (\"#\" ile biter)</div>
        </div>
      </v-card-text>
    </v-card>
  </div>
</template>

<script setup lang="ts">
import { usePageTitleStore } from '@/stores/pageTitle'
import { computed, onBeforeUnmount, reactive, ref } from 'vue'

const pageTitle = usePageTitleStore()
pageTitle.setTitle('Seri Port (CH340)')

// Bilgi/diagnostic toplayıcı (UI'da gösterilmiyor)
const rx = ref('')
const tx = ref('')
const baudRates = [9600, 19200, 38400, 57600, 115200]
const baudRate = ref<number>(9600)
const busy = ref(false)
const isConnected = ref(false)

// Görselleştirme durumları
const states = reactive({
  switchOn: false,   // 1. kutu (yeşil)
  machineRun: false, // 2. kutu (yeşil)
  pulse: false,      // 3. kutu (mavi) → rising edge ile sayaç++
})

const counter = ref(0)
const prevPulse = ref(false)
const incomingBuffer = ref('')

const firstBoxClass = computed(() => states.switchOn ? 'status-green' : 'status-gray')
const secondBoxClass = computed(() => states.machineRun ? 'status-green' : 'status-gray')
const thirdBoxClass = computed(() => states.pulse ? 'status-blue' : 'status-gray')

let port: SerialPort | null = null
let reader: ReadableStreamDefaultReader<Uint8Array> | null = null
let writer: WritableStreamDefaultWriter<Uint8Array> | null = null
let readLoopAbort: AbortController | null = null

const encoder = new TextEncoder()
const decoder = new TextDecoder()

function supportsWebSerial() {
  return typeof navigator !== 'undefined' && 'serial' in navigator
}

async function connect() {
  if (!supportsWebSerial()) {
    rx.value += '\nTarayıcı Web Serial desteklemiyor. Lütfen Chrome/Edge kullanın ve HTTPS üzerinden bağlanın.'
    return
  }
  try {
    busy.value = true
    // Kullanıcıdan CH340/Arduino portunu seçmesini ister
    // @ts-expect-error - Navigator.serial tipleri tarayıcıda mevcut
    const selectedPort: SerialPort = await navigator.serial.requestPort({
      filters: [
        // CH340 VID/PID sık değişebiliyor; filtre koymadan genel bırakıyoruz
        // { usbVendorId: 0x1A86 } // WCH (CH340) vendor id
      ],
    })
    port = selectedPort
    await port.open({ baudRate: baudRate.value })

    // Writer
    const textEncoderStream = new TransformStream({
      transform(chunk, controller) {
        controller.enqueue(encoder.encode(chunk))
      },
    })

    const writable = port.writable
    if (!writable) throw new Error('Yazma akışı yok')
    writer = textEncoderStream.writable.getWriter()
    textEncoderStream.readable.pipeTo(writable).catch(() => { })

    // Reader
    const textDecoderStream = new TransformStream({
      transform(chunk, controller) {
        controller.enqueue(decoder.decode(chunk, { stream: true }))
      },
    })

    const readable = port.readable
    if (!readable) throw new Error('Okuma akışı yok')

    readLoopAbort = new AbortController()
    const readerStream = readable
      .pipeThrough(new TextDecoderStream())
      .pipeThrough(new TransformStream({
        transform(chunk, controller) {
          controller.enqueue(String(chunk))
        },
      }))
    reader = readerStream.getReader({ signal: readLoopAbort.signal as any })

    isConnected.value = true
    rx.value += `\nBağlandı: ${String(port)}`

    readLoop().catch(err => {
      rx.value += `\nOkuma hatası: ${err?.message || err}`
      disconnect()
    })
  } catch (err: any) {
    rx.value += `\nBağlanılamadı: ${err?.message || err}`
  } finally {
    busy.value = false
  }
}

async function readLoop() {
  while (isConnected.value && reader) {
    const { value, done } = await reader.read()
    if (done) break
    if (value) handleIncoming(String(value))
  }
}

async function send() {
  try {
    if (!isConnected.value || !writer) return
    const line = tx.value.endsWith('\n') ? tx.value : `${tx.value}\n`
    const data = encoder.encode(line)
    // Doğrudan seri porta yaz
    const writable = port?.writable
    if (!writable) return
    const writerRaw = writable.getWriter()
    await writerRaw.write(data)
    writerRaw.releaseLock()
    tx.value = ''
  } catch (err: any) {
    rx.value += `\nGönderme hatası: ${err?.message || err}`
  }
}

async function disconnect() {
  try {
    isConnected.value = false
    readLoopAbort?.abort()
    await reader?.cancel().catch(() => { })
    reader?.releaseLock()
    const s = await port?.getSignals?.()
    await port?.close()
    rx.value += `\nBağlantı kesildi.`
  } catch (err: any) {
    rx.value += `\nKapatma hatası: ${err?.message || err}`
  } finally {
    reader = null
    writer = null
    port = null
  }
}

onBeforeUnmount(() => {
  if (isConnected.value) disconnect()
})

// ---- Yardımcılar ----
function handleIncoming(chunk: string) {
  // Arduino'dan akış: çerçeve '#'
  incomingBuffer.value += chunk
  while (true) {
    const hashIndex = incomingBuffer.value.indexOf('#')
    if (hashIndex === -1) break
    const frame = incomingBuffer.value.slice(0, hashIndex)
    incomingBuffer.value = incomingBuffer.value.slice(hashIndex + 1)
    processFrame(frame)
  }
}

function processFrame(frame: string) {
  // Örn: "1|0|1" → [switchOn, machineRun, pulse]
  const parts = frame.trim().split('|')
  if (parts.length < 8) return

  const v0 = parts[0].trim() === '1'
  const v1 = parts[1].trim() === '1'
  const v2 = parts[6].trim() === '1'

  states.switchOn = v0
  states.machineRun = v1

  // Rising edge detection for pulse → sayaç++
  if (v2 && !prevPulse.value) counter.value += 1
  states.pulse = v2
  prevPulse.value = v2
}
</script>

<style scoped>
.status-box {
  inline-size: 50px;
  block-size: 50px;
  border-radius: 6px;
  border: 1px solid rgba(0, 0, 0, 0.08);
}
.status-gray { background: #9e9e9e; }
.status-green { background: #4caf50; }
.status-blue { background: #2196f3; }
</style>
