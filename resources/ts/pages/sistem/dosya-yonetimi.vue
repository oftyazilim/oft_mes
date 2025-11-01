<template>
  <div
    style="
      padding: 32px;
      border: 1px solid #eee;
      margin: auto;
      background: #fff;
      max-inline-size: 1200px;
"
  >
    <DxFileManager
      :file-system-provider="fileSystemData"
      :on-selected-file-opened="displayImagePopup"
      :on-selection-changed="onSelectionChanged"
      :on-toolbar-preparing="onToolbarPreparing"
      :key="fmKey"
      current-path=""
    >
      <DxPermissions
        :create="true"
        :copy="true"
        :move="true"
        :delete="true"
        :rename="true"
        :upload="true"
        :download="true"
      />
            <DxToolbar>
        <DxToolbarItem name="create" />
        <DxToolbarItem name="upload" />
        <DxToolbarItem name="download" />
        <DxToolbarItem name="delete" />
        <DxToolbarItem name="refresh" />
      </DxToolbar>
    </DxFileManager>
    <DxPopup
      :hide-on-outside-click="true"
      :show-close-button="true"
      v-model:visible="popupVisible"
      :title="galleryFiles[galleryIndex]?.name || ''"
      :width="popupWidth + 50"
      :height="popupHeight"
      class="photo-popup-content"
    >
      <DxGallery
        :items="galleryFiles"
        :selected-index="galleryIndex"
        :show-indicator="true"
        :show-nav-buttons="true"
        :height="popupHeight - 80"
        :width="popupWidth"
        :loop="true"
        :item-template="galleryItemTemplate"
      />
    </DxPopup>
  </div>
</template>

<script setup lang="ts">
import axios from "axios";
import { DxFileManager, DxPermissions, DxToolbar, DxToolbarItem } from "devextreme-vue/file-manager";
import { DxGallery } from "devextreme-vue/gallery";
import { DxPopup } from "devextreme-vue/popup";
import { onMounted, ref } from "vue";

const popupVisible = ref(false);
const fileSystemData = ref<any[]>([]);
const selectedFile = ref<any>(null);
const fmKey = ref(0);
const popupWidth = ref(Math.min(window.innerWidth * 0.9, 1200));
const popupHeight = ref(Math.min(window.innerHeight * 0.9, 900));

// Galeri için
const galleryFiles = ref<Array<{ name: string; url: string }>>([]);
const galleryIndex = ref(0);

onMounted(async () => {
  const res = await axios.get("/api/file-manager");
  console.log("API file-manager response:", res.data);
  fileSystemData.value = [{
    name: "root",
    isDirectory: true,
    items: Array.isArray(res.data) ? res.data : [res.data],
    key: "",
    relativeName: ""
  }];
  // Force FileManager re-render in case it didn't pick up the initial data
  fmKey.value++;
  console.log("fileSystemData (wrapped):", fileSystemData.value);
});

function onSelectionChanged(e: any) {
  selectedFile.value = e.selectedItems && e.selectedItems.length ? e.selectedItems[0] : null;
}

function directDownload(file: any) {
  const url = file.url || (file.relativeName ? window.location.origin + '/storage/' + file.relativeName : null);
  if (!url) return;
  const a = document.createElement('a');
  a.href = url;
  a.download = file.name || '';
  document.body.appendChild(a);
  a.click();
  a.remove();
}

function onToolbarPreparing(e: any) {
  const items = e.toolbarOptions.items;
  for (const item of items) {
    if (item.name === 'download') {
      // override default download action with directDownload fallback
      const originalOnClick = item.onClick;
      item.onClick = (args: any) => {
        // try direct download for selected file
        if (selectedFile.value && (selectedFile.value.url || selectedFile.value.relativeName)) {
          directDownload(selectedFile.value);
        } else if (originalOnClick) {
          originalOnClick(args);
        }
      };
    }
  }
}


function displayImagePopup(e: any) {
  const parentKey =
    e.file.parentPath ||
    e.file.key?.split("/").slice(0, -1).join("/") ||
    e.file.parentKey ||
    "";
  console.log("parentKey:", parentKey, "file:", e.file);
  // Klasördeki tüm dosyaları bul
  function findFiles(tree: any, parentKey: string): Array<any> {
    if (!tree) return [];
    const treePath = tree.relativeName || tree.key || tree.name;
    // Tam eşleşme veya son segment eşleşmesi
    if (
      treePath === parentKey ||
      treePath?.split("/").pop() === parentKey?.split("/").pop()
    ) {
      return (
        tree.items?.filter(
          (f: any) => !f.items && /\.(jpg|jpeg|png|gif)$/i.test(f.name)
        ) || []
      );
    }
    if (tree.items) {
      for (const item of tree.items) {
        const found = findFiles(item, parentKey);
        if (found.length) return found;
      }
    }
    return [];
  }
  let files: Array<any> = [];
  for (const root of fileSystemData.value) {
    files = findFiles(root, parentKey);
    if (files.length) break;
  }
  galleryFiles.value = files.map((f: any) => {
    const path = f.relativeName || f.key || "";
    return {
      name: f.name,
      url: path ? window.location.origin + "/storage/" + path : "",
    };
  });
  console.log("galleryFiles:", galleryFiles.value);
  const selectedUrl =
    window.location.origin +
    "/storage/" +
    (e.file.relativeName || e.file.key || "");
  galleryIndex.value = galleryFiles.value.findIndex(
    (f: any) => f.url === selectedUrl
  );
  popupVisible.value = true;
}
function galleryItemTemplate(item: { url: string; name: string }) {
  return `<img src='${item.url}' alt='${item.name}' style='width:100%;height:100%;object-fit:contain;' />`;
}
</script>

<style scoped>
.photo-popup-content {
  text-align: center;
}

.photo-popup-content .photo-popup-image {
  block-size: 100%;
  max-inline-size: 100%;
}
</style>
