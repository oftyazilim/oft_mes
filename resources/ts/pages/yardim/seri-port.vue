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

        <v-textarea v-model="rx" label="Gelen Veri" rows="12" readonly auto-grow />
      </v-card-text>
    </v-card>
  </div>
</template>

<script setup lang="ts">
import { usePageTitleStore } from '@/stores/pageTitle'
import { onBeforeUnmount, ref } from 'vue'

const pageTitle = usePageTitleStore()
pageTitle.setTitle('Seri Port (CH340)')

const rx = ref('')
const tx = ref('')
const baudRates = [9600, 19200, 38400, 57600, 115200]
const baudRate = ref<number>(115200)
const busy = ref(false)
const isConnected = ref(false)

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
    if (value) rx.value += String(value)
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
</script>
