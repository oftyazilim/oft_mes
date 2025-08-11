<template>
  <v-container>
    <div class="tables">
      <Grid :tasks-store="tasksStore" :status="1" class="column" />
      <Grid :tasks-store="tasksStore" :status="2" class="column" />
    </div>
  </v-container>
</template>

<script setup lang="ts">
import Grid from "@/components/dialogs/Grid.vue";



</script>

<style scoped>
.tables {
  display: flex;
}

.column:first-child {
  width: 50%;
  padding-right: 15px;
}

.column:last-child {
  width: 50%;
  padding-left: 15px;
}

.selected-item {
  background-color: #f0f0f0;
  cursor: pointer;
}
</style>
