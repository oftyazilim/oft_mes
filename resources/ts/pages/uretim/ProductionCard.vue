<template>
  <VCard class="pa-3 production-card" elevation="3" rounded="lg" @click="emitDetails"
    :class="{ 'selected-card': isSelected }">
    <!-- İşlem Süreci Overlay -->
    <div v-if="actionLoading" class="loading-overlay d-flex flex-column align-center justify-center">
      <v-progress-circular indeterminate color="primary" size="40" class="mb-3" />
      <span style="font-weight: 600;">İşlem yapılıyor...</span>
    </div>
    <!-- Üst Bar -->
    <VRow align="center" justify="space-between" class="mb-0" :style="{
      backgroundColor: getHeaderColor,
      color: 'white',
      padding: '0px 8px',
      borderRadius: '3px 3px 0 0',
    }">
      <span style="font-size: 18px; padding-block: 2px; padding-inline: 8px;">
        {{ isemriNo }} &nbsp;
        <span style="font-size: 16px;">{{
          sebep != null ? sebep.substring(0, 30) : ""
        }}</span>
      </span>
      <span class="time-display">{{ sonDurumSuresi }}</span>
    </VRow>

    <VRow class="ma-0 mt-2" align="center" justify="space-between">
      <VCol cols="10" class="text-center pa-2 pt-0">
        <!-- Verimlilik ve Kalite -->
        <VRow class="text-center pa-0 my-1">
          <VCol cols="4" class="pa-1">
            <div :style="{ fontSize: '28px', color: '#429193' }">
              {{ totalTime }}
            </div>
            <div style="font-size: 14px;">Toplam Süre</div>
          </VCol>
          <VCol cols="4" class="pa-1">
            <div style="font-size: 28px;">{{ totalDown }}</div>
            <div style="font-size: 14px;">Toplam Duruş</div>
          </VCol>
          <VCol cols="4" class="pa-1 mt-0 pt-0 ps-0">
            <div class="text-center" style="margin-block: -12px -10px;">
              <VueApexCharts :options="chartOptions" :series="series" type="radialBar" :height="125" />
              <div style="font-size: 14px; margin-block-start: -8px;">MEÇO %</div>
            </div>
          </VCol>
        </VRow>

        <!-- Çok renkli bar -->
        <div id="bar">
          <div v-for="(segment, index) in barData" :key="index" class="bar-segment" :style="getSegmentStyle(segment)"
            :title="getSegmentTooltip(segment)"></div>
        </div>

        <!-- Alt Bilgi -->
        <VRow justify="space-between" style="font-size: 14px;" class="ma-0 mt-1">
          <span>{{ partName.substring(0, 30) }}</span>
          <span>{{ partCode }}</span>
        </VRow>

      </VCol>

      <!-- Butonlar -->
      <VCol cols="1" class="pa-0">
        <VBadge :content="props.ekipSayisi" bordered :location="'start'" offset-x="-5" offset-y="5"
          class="v-badge--tonal">
          <VBtn :disabled="props.personelId != userData.id" icon="tabler-users-plus" variant="tonal" color="primary"
            rounded class="mb-1" id="ekipSec" @click="ekipSecDialog = true" />
        </VBadge>
        <DxTooltip :hide-on-outside-click="false" :target="`#ekipSec`" show-event="mouseenter" hide-event="mouseleave">
          Ekip Seç
        </DxTooltip>
        <VBtn :disabled="Number(props.kontrolGerekli) === 1 || props.personelId != userData.id" icon="tabler-phone"
          variant="tonal" color="primary" rounded class="ps-1 mb-1" id="kaliteCagir"
          @click="guncelleKontrolDurumu(!props.kontrolcuCagrildi)" />
        <DxTooltip :hide-on-outside-click="false" :target="`#kaliteCagir`" show-event="mouseenter"
          hide-event="mouseleave">
          Kaliteci Çağır
        </DxTooltip>
        <VBtn :disabled="props.personelId != userData.id" icon="tabler-logout" variant="tonal" color="error" rounded
          class="mb-0" id="kapat" @click="showUretimMiktariDialog = true" />
        <DxTooltip :hide-on-outside-click="false" :target="`#kapat`" show-event="mouseenter" hide-event="mouseleave">
          İş Emrini Kapat
        </DxTooltip>
      </VCol>
      <VCol cols="1" class="pa-0" style="margin-block-start: 4px;">
        <VBtn :disabled="props.personelId != userData.id || status === 'Çalışıyor'" icon="tabler-play" variant="tonal"
          color="success" rounded class="mb-1" id="calisma" @click="durumuGuncelle('ÇALIŞIYOR')" />
        <DxTooltip :hide-on-outside-click="false" :target="`#calisma`" show-event="mouseenter" hide-event="mouseleave">
          Çalışmaya Başla
        </DxTooltip>
        <VBtn :disabled="props.personelId != userData.id" icon="tabler-pause" variant="tonal" color="warning" rounded
          class="mb-1" id="durus" @click="durumuGuncelle('DURUYOR')" />
        <DxTooltip :hide-on-outside-click="false" :target="`#durus`" show-event="mouseenter" hide-event="mouseleave">
          Duruşa Geç
        </DxTooltip>
        <VBtn :disabled="props.personelId != userData.id" icon="tabler-settings-share" variant="tonal" color="info"
          rounded class="mb-1" id="hazirlik" @click="durumuGuncelle('HAZIRLIK')" />
        <DxTooltip :hide-on-outside-click="false" :target="`#hazirlik`" show-event="mouseenter" hide-event="mouseleave">
          Hazırlığa Geç
        </DxTooltip>

      </VCol>

    </VRow>
    <hr>

    <!-- En Alt Bilgi (Notlar) -->
    <VCol class="pa-0 ma-0">
      <VRow class="pa-0 ma-0" style="margin-block-end: -20px;">
        <VCol cols="2" class="ps-2 pt-2 pe-0">
          <span style="font-weight: bold;">Pln. Notu</span>
        </VCol>
        <VCol cols="10" class="ps-2 pt-2 pe-0">
          <span style="font-style: italic;"> : &nbsp;{{ plnNote }}</span>
        </VCol>
      </VRow>
      <VRow class="pa-0 ma-0">
        <VCol cols="2" class="ps-2 pt-2 pe-0" style="margin-block-start: -20px;">
          <span style="font-weight: bold;">Ürt. Notu</span>
        </VCol>
        <VCol cols="10" class="ps-2 pt-2 pe-0" style="margin-block-start: -20px;">
          <span style="font-style: italic;"> : &nbsp;{{ prdNote }}</span>
        </VCol>
      </VRow>
      <VRow class="pa-0 ma-0">
        <VCol cols="2" class="ps-2 pt-2 pe-0" style="margin-block-start: -20px;">
          <span style="font-weight: bold;">Başlatan</span>
        </VCol>
        <VCol cols="4" class="ps-2 pt-2 pe-0" style="margin-block-start: -20px;">
          <span style="font-style: italic;"> : &nbsp;{{ personelName }}</span>
        </VCol>
        <VCol cols="2" class="ps-2 pt-2 pe-0" style="margin-block-start: -20px;">
          <span style="font-weight: bold;">Başlangıç</span>
        </VCol>
        <VCol cols="4" class="ps-2 pt-2 pe-0" style="margin-block-start: -20px;">
          <span style="font-style: italic;"> : &nbsp;{{ baslangicZamani }}</span>
        </VCol>
      </VRow>

      <VRow class="pa-0 ma-0 pe-2">
        <VCol cols="4" class="ps-2 pt-1 pe-0 text-center" style=" margin-block: -10px -17px;">
          <div :style="{
            padding: '5px',
            backgroundColor: props.kontrolGerekli === true ? 'gold' : 'transparent',
            fontWeight: 'bold',
            width: '100%',
            borderRadius: '5px',
            border: '1px solid gold',
            color: props.kontrolGerekli === true ? 'black' : '#999',
          }">
            {{ props.kontrolGerekli === true ? 'Kontrol Gerekli' : 'Kontrol Gereksiz' }}
          </div>
        </VCol>
        <VCol cols="4" class="ps-2 pt-1 pe-0 text-center" style=" margin-block: -10px -17px;">
          <div :style="{
            padding: '5px',
            backgroundColor: props.kontrolcuCagrildi === true ? 'gold' : 'transparent',
            fontWeight: 'bold',
            width: '100%',
            borderRadius: '5px',
            border: '1px solid gold',
            color: props.kontrolcuCagrildi === true ? 'black' : '#999',
          }">
            {{ props.kontrolcuCagrildi === true ? 'Kaliteci çağrıldı' : 'Kaliteci çağrılmadı' }}
          </div>
        </VCol>
        <VCol cols="4" class="ps-2 pt-1 pe-0 text-center" style=" margin-block: -10px -17px;">
          <div :style="{
            padding: '5px',
            backgroundColor: props.aksesuarli === '1' ? 'gold' : 'transparent',
            fontWeight: 'bold',
            width: '100%',
            borderRadius: '5px',
            border: '1px solid gold',
            color: props.aksesuarli === '1' ? 'black' : '#999',
          }">
            {{ props.aksesuarli === '1' ? 'Aksesuarlı' : 'Aksesuarsız' }}
          </div>
        </VCol>
      </VRow>
    </VCol>
  </VCard>

  <DxPopup v-model:visible="popupDurusSecGosterVisible" :width="500" :height="240" :hide-on-outside-click="false"
    :show-close-button="false" :drag-enabled="false" :close-on-back-button="false" :defer-rendering="false"
    :focus-state-enabled="false" :shading="true" :shading-color="'rgba(0, 0, 0, 0.5)'" :on-hiding="onPopupHiding"
    titleTemplate="title">
    <template #content>
      <VRow class="pa-4">
        <AppSelect v-model="selectedSebep" :items="durusSebepleri" item-title="description"
          item-value="break_reason_code" return-object label="Duruş Sebebi" single-line placeholder="Seçiniz..."
          variant="outlined" />
      </VRow>
    </template>

    <template #title>
      <p class="popup-title">Duruş Sebebini Giriniz</p>
    </template>

    <DxToolbarItem widget="dxButton" toolbar="bottom" location="center" :options="{
      ...kaydetOptions,
      onClick: durusSebebiKaydet,
    }" />
    <br />

  </DxPopup>

  <DxPopup v-model:visible="showUretimMiktariDialog" :width="400" :height="240" :hide-on-outside-click="false"
    :show-close-button="true">
    <template #title>
      <p class="popup-title">Değerleri Giriniz</p>
    </template>
    <template #content>
      <VCardTitle>Üretim Miktarı Giriniz</VCardTitle>
      <VTextField v-model="uretimMiktari" type="number" />
    </template>
    <DxToolbarItem widget="dxButton" toolbar="bottom" location="center" :options="kaydetOptions" @click="kapat" />
    <DxToolbarItem widget="dxButton" toolbar="bottom" location="center" :options="vazgecOptions" />
  </DxPopup>

  <PersonelSecDialog v-model="ekipSecDialog" :isemriID="Number(props.isemriId)"
    :istasyon-id="Number(userData.istasyon_id)" :guid="props.guid" @iptal="console.log('Dialog kapatıldı')"
    @kaydedildi="handleDialogKayit" />
</template>


<script setup lang="ts">
import { hexToRgb } from '@layouts/utils';
import axios from "axios";
import { DxPopup, DxToolbarItem } from "devextreme-vue/popup";
import { DxTooltip } from "devextreme-vue/tooltip";
import notify from "devextreme/ui/notify";
import { computed, onMounted, watch } from "vue";
import { useTheme } from 'vuetify';
import PersonelSecDialog from "./PersonelSecDialog.vue";

const emit = defineEmits(['show-details', 'ekipGuncellendi', 'panelKapatildi', 'durusGirildi']);
const actionLoading = ref(false);
function afterActionRefresh() {
  emit('ekipGuncellendi');
}
// Herhangi bir buton tıklamasında üst bileşene tetik gönderme helper'i
function triggerGlobalRefresh() {
  emit('ekipGuncellendi');
}

const showUretimMiktariDialog = ref(false);
const uretimMiktari = ref(0);
const userData = useCookie<any>("userData");
const popupDurusSecGosterVisible = ref(false);
const durusSebepleri = ref([]);
const status = ref("");
const allowPopupClose = ref(true);
const ekipSecDialog = ref(false)

// Aktif ekipleri kapatıp ardından iş emrini kapat
const topluBitir = async () => {
  try {
    const { data } = await axios.get('/api/aktif-ekipler', {
      params: { isemriID: Number(props.isemriId), guid: props.guid }
    });
    const idList = (data || []).map((item: any) => item.id).filter((x: any) => x != null);
    if (!idList.length) return; // kapatılacak yok
    await axios.post('/api/ekip-bitir-toplu', { ids: idList });
    notify('Ekibin görevleri bitirildi.', 'success');
  } catch (e) {
    console.error(e);
    notify('Toplu bitirme sırasında hata oluştu.', 'error');
  }
};

const kapat = async () => {
  if (actionLoading.value) return;
  actionLoading.value = true;
  try {
    await topluBitir();
    await isEmriKapat();
    afterActionRefresh();
  } finally {
    actionLoading.value = false;
  }
}

const isEmriKapat = async () => {
  // console.log(props.isemriId, props.guid, userData.value.id, props.sebep, uretimMiktari.value)
  try {
    const response = await axios.post('/api/insert-workorder', {
      WorderMId: Number(props.isemriId),
      guid: props.guid,
      userId: userData.value.id,
      selectedDurus: props.sebep,
      uretimMiktari: uretimMiktari.value,
    })

    uretimMiktari.value = 0
    emit('panelKapatildi', props.isemriNo)
    triggerGlobalRefresh();
  } catch (err) {
    console.error(err)
  }
  showUretimMiktariDialog.value = false

}
const handleDialogKayit = () => {
  afterActionRefresh();
}

const selectedSebep = ref<{
  break_reason_code: string;
  description: string;
} | null>(null);

const emitDetails = () => {
  // Kartın detay bilgilerini üst bileşene gönder
  const detail = {
    isemriNo: props.isemriNo,
    isemriId: props.isemriId,
    partId: props.partId,
    partCode: props.partCode,
    partName: props.partName,
    guid: props.guid,
    status: status.value,
    sebep: props.sebep,
    baslangicZamani: props.baslangicZamani,
    personelId: props.personelId,
    personelName: props.personelName,
    kontrolGerekli: props.kontrolGerekli,
    kontrolcuCagrildi: props.kontrolcuCagrildi,
    plnNote: props.plnNote,
    prdNote: props.prdNote,
    ekipSayisi: props.ekipSayisi,
  };
  emit("show-details", detail);
};

const props = defineProps<{
  status: string;
  isemriId: string;
  isemriNo: string;
  runTime: string;
  totalDown: string;
  totalTime: string;
  sonDurumSuresi: string;
  target: number;
  actual: number;
  efficiency: number;
  quality: number;
  partId: number;
  partCode: string;
  partName: string;
  sebep: string;
  baslikArkarenk: string;
  barData: { type: string; duration: number; sebep?: string }[];
  barTotal: number;
  guid: string;
  isSelected: boolean;
  kontrolGerekli: boolean;
  kontrolcuCagrildi: boolean;
  plnNote: string;
  prdNote: string;
  personelId: number;
  personelName: string;
  baslangicZamani: string;
  aksesuarli: string;
  ekipSayisi: number;
}>();

const kaydetOptions = {
  width: 100,
  marginBottom: 30,
  type: "success",
  text: "Kaydet",
  stylingMode: "contained",
};

const vazgecOptions = {
  width: 100,
  type: "normal",
  text: "Vazgeç",
  stylingMode: "contained",
  onClick: () => {
    popupDurusSecGosterVisible.value = false;
    showUretimMiktariDialog.value = false;
  },
};

status.value = props.status;

const typeColorMap: Record<string, string> = {
  durus: "#ec5a58",
  ayar: "#a59ef7",
  calisma: "#6ABE63",
  mola: "#f4b73f",
  kalan: "lightgray",
};

const getSegmentStyle = (segment: { type: string; duration: number }) => {
  const color = typeColorMap[segment.type] || "gray";
  const widthPercent = (segment.duration / props.barTotal) * 100;
  return {
    backgroundColor: color,
    width: `${widthPercent}%`,
    borderRadius: "3px",
  };
};

type BarSegmentType = "durus" | "ayar" | "calisma" | "mola" | "kalan";
interface BarSegment {
  type: BarSegmentType;
  duration: number;
  sebep?: string;
}

// Temelden gelen barData 'string' tipli olabilir; gerçekte 5 sabit tipten biri.
// Güvenli dönüştürücü:
const coerceSegment = (s: { type: string; duration: number; sebep?: string }): BarSegment => {
  const allowed: BarSegmentType[] = ["durus", "ayar", "calisma", "mola", "kalan"];
  const t = allowed.includes(s.type as BarSegmentType) ? (s.type as BarSegmentType) : "kalan";
  return { type: t, duration: s.duration, sebep: s.sebep };
};

const typeLabelMap: Record<BarSegment["type"], string> = {
  durus: "Duruş Süreci",
  ayar: "Ayar Süreci",
  calisma: "Çalışma Süreci",
  mola: "Molada Geçen Süre",
  kalan: "Kalan Süre",
};

const getSegmentTooltip = (segmentInput: BarSegment | { type: string; duration: number; sebep?: string }) => {
  const segment = coerceSegment(segmentInput as any);
  const baseLabel = typeLabelMap[segment.type] || segment.type;
  if (segment.sebep != null) {
    return `${baseLabel}: ${segment.sebep}`;
  }
  return baseLabel;
};

const getHeaderColor = computed(() => {
  return typeColorMap[props.baslikArkarenk] || props.baslikArkarenk || "#999";
});

const durumuGuncelle = async (durum: any) => {
  if (status.value.toLocaleLowerCase() === durum.toLocaleLowerCase()) {
    console.log("Durum zaten bu:", durum);
    durusSebebiGir();
    return;
  } else {
    selectedSebep.value = durum === "HAZIRLIK" ? { break_reason_code: "", description: "HAZIRLIK ÇALIŞMASI" } : null;
    status.value =
      durum === "ÇALIŞIYOR"
        ? "Çalışıyor"
        : durum === "DURUYOR"
          ? "Duruyor"
          : durum === "HAZIRLIK"
            ? "Ayar"
            : durum;
    try {
      if (actionLoading.value) return; actionLoading.value = true;
      await axios.post("/api/durumKaydet", {
        isEmriId: props.isemriId,
        isEmriNo: props.isemriNo,
        urunID: props.partId,
        urunKodu: props.partCode,
        urunAdi: props.partName,
        durum: durum,
        vardiya: 2,
        istasyonKodu: userData.value.istasyon_id,
        userId: userData.value.id,
        personelSayisi: 0,
        selectedDurus: selectedSebep.value,
        guid: props.guid,
      });
      afterActionRefresh();
    } catch (error) {
      console.error("Kaydetme sırasında bir hata oluştu:", error);
    } finally {
      actionLoading.value = false;
    }

    if (durum === "DURUYOR") {
      durusSebebiGir();
    }
  }
};

const guncelleKontrolDurumu = async (yeniDeger: boolean | null) => {
  // console.log('kontrolcü çağır')
  if (yeniDeger === null) return;
  try {
    if (actionLoading.value) return; actionLoading.value = true;
    await axios.post('/api/kontrolcu-cagir-guncelle', {
      isEmriID: props.isemriId,
      istasyonID: userData.value.istasyon_id,
      personelID: userData.value.id,
      is_check_quality_opr: yeniDeger ? 1 : 0,
    })
    afterActionRefresh();

  } catch (error) {
    console.error('Kontrolcü durumu güncellenemedi:', error)
  } finally {
    actionLoading.value = false;
  }
}

const durusSebepleriniAl = async () => {
  try {
    const response = await axios.get("/api/durus-sebepleri-al", {
      params: { istasyon: userData.value.istasyon_id },
    });
    durusSebepleri.value = response.data.durusSebepleri;
  } catch (error) {
    console.error("Veri çekme hatası:", error);
  }
};

onMounted(() => {
  durusSebepleriniAl();
});

const durusSebebiGir = async () => {
  // durusSebepleriniAl();
  selectedSebep.value = { break_reason_code: "0000", description: "GİRİLMEDİ" };
  popupDurusSecGosterVisible.value = true;
};

const durusSebebiKaydet = async () => {
  if (!selectedSebep.value) {
    notify({
      message: "Lütfen bir duruş sebebi seçiniz.",
      type: "error",
      displayTime: 3000,
    });
    return;
  }
  try {
    const response = await axios.post("/api/durusKaydet", {
      isEmriId: props.isemriId,
      istasyonKodu: userData.value.istasyon_id,
      userId: userData.value.id,
      selectedDurus: selectedSebep.value,
    });
  } catch (error) {
    console.error("Kaydetme sırasında bir hata oluştu:", error);
  } finally {
    allowPopupClose.value = true;
    popupDurusSecGosterVisible.value = false;
  }
};

const onPopupHiding = (e: any) => {
  if (!allowPopupClose.value) {
    e.cancel = true; // ESC'yi veya dışarı tıklamayı engelle
  }
};

watch(popupDurusSecGosterVisible, (yeni, eski) => {
  if (eski === true && yeni === false) {
    emit('durusGirildi');
  }
});

const series = [props.efficiency || 0];
const vuetifyTheme = useTheme()

const chartOptions = computed(() => {
  const currentTheme = vuetifyTheme.current.value.colors
  const variableTheme = vuetifyTheme.current.value.variables

  return {
    chart: {
      sparkline: {
        enabled: true,
      },
      parentHeightOffset: 0,
      type: 'radialBar',
    },
    colors: ['rgba(var(--v-theme-warning), 1)'],
    plotOptions: {
      radialBar: {
        offsetY: 0,
        startAngle: -90,
        endAngle: 90,
        hollow: {
          size: '60%',
        },
        track: {
          strokeWidth: '40%',
          background: 'rgba(var(--v-track-bg))',
        },
        dataLabels: {
          name: {
            show: false,
          },
          value: {
            fontSize: '24px',
            color: '#429193',
            fontWeight: 500,
            offsetY: -8,
            formatter: function (value: number) {
              return value;
            }
          },
        },
      },
    },
    grid: {
      show: false,
      padding: {
        bottom: 5,
      },
    },
    stroke: {
      lineCap: 'round',
    },
    labels: ['Progress'],
    responsive: [
      {
        breakpoint: 1442,
        options: {
          chart: {
            height: 125,
          },
          plotOptions: {
            radialBar: {
              dataLabels: {
                value: {
                  fontSize: '20px',
                  fontWeight: 500,
                  color: `rgba(${hexToRgb(currentTheme['on-background'])},${variableTheme['high-emphasis-opacity']})`,
                },
              },
              hollow: {
                size: '60%',
              },
            },
          },
        },
      },
      {
        breakpoint: 1370,
        options: {
          chart: {
            height: 120,
          },
        },
      },
      {
        breakpoint: 1280,
        options: {
          chart: {
            height: 200,
          },
          plotOptions: {
            radialBar: {
              dataLabels: {
                value: {
                  fontSize: '18px',
                },
              },
              hollow: {
                size: '70%',
              },
            },
          },
        },
      },
      {
        breakpoint: 960,
        options: {
          chart: {
            height: 250,
          },
          plotOptions: {
            radialBar: {
              hollow: {
                size: '70%',
              },
              dataLabels: {
                value: {
                  fontSize: '24px',
                },
              },
            },
          },
        },
      },
    ],
  }
})
</script>

<style scoped>
.selected-card {
  border: 4px solid yellow;
}

#bar {
  display: flex;
  overflow: hidden;
  border-radius: 4px;
  block-size: 20px;
  gap: 1px;
  inline-size: 100%;
}

.time-display {
  color: "white";
  font-size: 20px;
  font-weight: 500;
  inline-size: fit-content;
  padding-block: 0;
  padding-inline: 12px;
}

.popup-title {
  background-color: #393378;
  color: white;
  font-size: 20px;
  font-weight: bold;
}

.production-card {
  position: relative;
  overflow: hidden;
}

.loading-overlay {
  position: absolute;
  z-index: 30;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  background: rgba(255, 255, 255, 85%);
  inset: 0;
}
</style>
