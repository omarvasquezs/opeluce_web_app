<template>
  <div class="opeluce-main-layout">
    <div class="container mt-4">
      <router-view />
    </div>
  </div>
</template>

<script setup>

</script>

<style scoped>
.opeluce-main-layout {
  /* Add bottom padding to prevent footer overlap */
  padding-bottom: 90px; /* 70px footer height + 20px extra spacing */
  min-height: calc(100vh - 70px); /* Ensure minimum height accounting for footer */
}

/* Additional responsive adjustments */
@media (max-width: 768px) {
  .opeluce-main-layout {
    padding-bottom: 100px; /* 80px footer height + 20px extra spacing on mobile */
    min-height: calc(100vh - 80px);
  }
}
</style>
