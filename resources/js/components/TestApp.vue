<template>
  <div class="p-4 bg-blue-100 text-center">
    <h1 class="text-2xl font-bold text-blue-800">Vue App is Working!</h1>
    <p class="mt-2">{{ message }}</p>
    <button @click="increment" class="mt-4 px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
      Count: {{ count }}
    </button>
  </div>
</template>

<script>
export default {
  name: 'TestApp',
  data() {
    return {
      message: 'If you can see this, Vue is working correctly!',
      count: 0
    }
  },
  methods: {
    increment() {
      this.count++;
    }
  },
  mounted() {
    console.log('TestApp mounted successfully');
  }
}
</script>
