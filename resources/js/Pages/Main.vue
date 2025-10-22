<template>
  <div>
    <EmployeePage v-if="$page?.props?.auth.user?.role === 'employee'" />
  </div>
</template>
<script setup>
import EmployeePage from './Employee/Index.vue'
</script>