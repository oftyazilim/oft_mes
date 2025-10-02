<template>
  <!-- Gantt bileşeni -->
  <DxGantt ref="ganttRef" :task-list-width="500" :height="700" scale-type="weeks" :root-value="-1">
    <DxTasks :data-source="tasks" />
    <DxDependencies :data-source="dependencies" />
    <DxResources :data-source="resources" />
    <DxResourceAssignments :data-source="resourceAssignments" />

    <DxToolbar>
      <DxItem name="undo" />
      <DxItem name="redo" />
      <DxItem name="separator" />
      <DxItem name="collapseAll" />
      <DxItem name="expandAll" />
      <DxItem name="separator" />
      <DxItem name="addTask" />
      <DxItem name="deleteTask" />
      <DxItem name="separator" />
      <DxItem name="zoomIn" />
      <DxItem name="zoomOut" />
      <DxItem :options="exportButtonOptions" widget="dxButton" />
    </DxToolbar>

    <DxEditing :enabled="true" />
    <DxValidation :auto-update-parent-tasks="true" />

    <DxGanttColumn :width="300" data-field="title" caption="Subject" />
    <DxGanttColumn data-field="start" caption="Start Date" />
    <DxGanttColumn data-field="end" caption="End Date" />
  </DxGantt>

  <div class="options">
    <div class="column">
      <div class="caption">Export Options</div>
      <div class="option">
        <div class="label">Document format:</div>
        <div class="value">
          <DxSelectBox :items="formats" :input-attr="{ 'aria-label': 'Format' }" v-model:value="formatBoxValue" />
        </div>
      </div>
      <div class="option">
        <div class="label">Landscape orientation:</div>
        <div class="value">
          <DxCheckBox v-model:value="landscapeCheckBoxValue" />
        </div>
      </div>
      <div class="option">
        <div class="label">Export mode:</div>
        <div class="value">
          <DxSelectBox :items="exportModes" :input-attr="{ 'aria-label': 'Export Mode' }"
            v-model:value="exportModeBoxValue" />
        </div>
      </div>
      <div class="option">
        <div class="label">Date range:</div>
        <div class="value">
          <DxSelectBox :items="dateRanges" v-model:value="dateRangeBoxValue"
            :input-attr="{ 'aria-label': 'Date Range' }" @value-changed="dateRangeBoxSelectionChanged($event)" />
        </div>
      </div>
    </div>
    <div class="column">
      <div class="caption">Task Filter Options</div>
      <div class="option">
        <div class="label">Start task (index):</div>
        <div class="value">
          <DxNumberBox :disabled="customRangeDisabled" :value="startTaskIndex" :min="0" :max="endTaskIndex"
            :show-spin-buttons="true" :input-attr="{ 'aria-label': 'Start Task Index' }"
            @value-changed="startTaskIndexChanged" />
        </div>
      </div>
      <div class="option">
        <div class="label">End task (index):</div>
        <div class="value">
          <DxNumberBox :disabled="customRangeDisabled" :value="endTaskIndex" :min="startTaskIndex"
            :max="tasks.length - 1" :show-spin-buttons="true" :input-attr="{ 'aria-label': 'End Task Index' }"
            @value-changed="endTaskIndexChanged" />
        </div>
      </div>
      <div class="option">
        <div class="label">Start date:</div>
        <div class="value">
          <DxDateBox :disabled="customRangeDisabled" :input-attr="{ 'aria-label': 'Start Date' }"
            v-model:value="startDate" :max="endDate" type="date" apply-value-mode="useButtons" />
        </div>
      </div>
      <div class="option">
        <div class="label">End date:</div>
        <div class="value">
          <DxDateBox :disabled="customRangeDisabled" :input-attr="{ 'aria-label': 'End Date' }" v-model:value="endDate"
            :min="startDate" type="date" apply-value-mode="useButtons" />
        </div>
      </div>
    </div>
  </div>



</template>

<script setup lang="ts">
import "devexpress-gantt/dist/dx-gantt.min.css";
import DxCheckBox from "devextreme-vue/check-box";
import DxDateBox from "devextreme-vue/date-box";
import {
  DxDependencies,
  DxEditing,
  DxGantt,
  DxColumn as DxGanttColumn,
  DxItem,
  DxResourceAssignments,
  DxResources,
  DxTasks,
  DxToolbar,
} from "devextreme-vue/gantt";
import DxNumberBox from "devextreme-vue/number-box";
import DxSelectBox from "devextreme-vue/select-box";

// import type { GanttPdfExportDateRange, GanttPdfExportMode } from 'devextreme/ui/gantt';
import "jspdf-autotable";
import { computed, ref } from 'vue';
import {
  dateRanges,
  exportModes,
  formats,
  tasks
} from './data';

const ganttRef = ref();

const exportButtonOptions = {
  hint: "Export to PDF",
  icon: "exportpdf",
  stylingMode: "text",
  onClick: () => {
    // exportGantt();
  },
};
const formatBoxValue = ref(formats[0]);
const exportModeBoxValue = ref(exportModes[0]);
const dateRangeBoxValue = ref(dateRanges[1]);
const landscapeCheckBoxValue = ref(true);
const startTaskIndex = ref(0);
const endTaskIndex = ref(3);
const startDate = ref(tasks[0].start);
const endDate = ref(tasks[0].end);
const customRangeDisabled = ref(true);

// async function exportGantt() {
//   const format = formatBoxValue.value.toLowerCase();
//   const isLandscape = landscapeCheckBoxValue.value;
//   const exportMode: GanttPdfExportMode =
//     exportModeBoxValue.value === "Tree List"
//       ? "treeList"
//       : (exportModeBoxValue.value.toLowerCase() as GanttPdfExportMode);
//   const dataRangeMode = (dateRangeBoxValue.value.toLowerCase() as 'all' | 'visible' | 'custom');
//   let dateRangeOpt: GanttPdfExportDateRange | {
//     startIndex: number;
//     endIndex: number;
//     startDate: Date;
//     endDate: Date;
//   } | undefined;
//   if (dataRangeMode === 'custom') {
//     dateRangeOpt = {
//       startIndex: startTaskIndex.value,
//       endIndex: endTaskIndex.value,
//       startDate: startDate.value,
//       endDate: endDate.value,
//     };
//   } else {
//     dateRangeOpt = dataRangeMode;
//   }
//   const doc = await exportGanttToPdf({
//     component: ganttRef.value.instance,
//     createDocumentMethod: (args) => new jsPDF(args),
//     format,
//     landscape: isLandscape,
//     exportMode,
//     // Tip uyuşmazlığını aşmak için dateRange'i uygun tipe cast ediyoruz
//     dateRange: dateRangeOpt,
//   });

//   doc.save("gantt.pdf");
// }
function dateRangeBoxSelectionChanged(e: any) {
  customRangeDisabled.value = e.value !== "Custom";
}
function startTaskIndexChanged(e: any) {
  startTaskIndex.value = e.value;
}
function endTaskIndexChanged(e: any) {
  endTaskIndex.value = e.value;
}
</script>

<style scoped>
#gantt {
  block-size: 700px;
}

.options {
  display: flex;
  background-color: rgb(191 191 191 / 15%);
  margin-block-start: 20px;
}

.column {
  display: flex;
  flex-direction: column;
  inline-size: 40%;
  margin-block: 15px;
  margin-inline: 3%;
  text-align: start;
}

.option {
  display: flex;
  align-items: center;
  padding-block: 5px;
  padding-inline: 0;
}

.label,
.value {
  display: inline-block;
  vertical-align: middle;
}

.label {
  inline-size: 180px;
}

.value {
  inline-size: 30%;
}

.caption {
  font-size: 18px;
  font-weight: 500;
}

#employees {
  block-size: 440px;
  margin-block-start: 30px;
}
</style>
