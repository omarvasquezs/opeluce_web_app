<template>
  <div class="d-flex justify-content-center align-items-center vh-100">
    <div class="card p-4" style="max-width: 400px; width: 100%;">
      <h3 class="card-title text-center mb-3">Opeluce Admin Login</h3>
      <form @submit.prevent="submitLogin">
        <div class="mb-3">
          <label for="username" class="form-label">Username</label>
          <input v-model="form.username" type="text" id="username" class="form-control" required />
        </div>
        <div class="mb-3">
          <label for="password" class="form-label">Password</label>
          <input v-model="form.password" type="password" id="password" class="form-control" required />
        </div>
        <div class="mb-3 form-check">
          <input v-model="form.remember" type="checkbox" id="remember" class="form-check-input" />
          <label for="remember" class="form-check-label">Remember me</label>
        </div>
        <div v-if="error" class="alert alert-danger">{{ error }}</div>
        <button type="submit" class="btn btn-primary w-100">Login</button>
      </form>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue';
import axios from 'axios';
import { useRouter } from 'vue-router';

const router = useRouter();
const form = reactive({
  username: '',
  password: '',
})
const error = ref('');

async function submitLogin() {
  error.value = '';
  try {
    await axios.post('/login', {
      username: form.username,
      password: form.password,
      remember: form.remember,
    });
    router.push('/');
  } catch (e) {
    if (e.response && e.response.data && e.response.data.message) {
      error.value = e.response.data.message;
    } else {
      error.value = 'Login failed. Please check your credentials.';
    }
  }
}
</script>

<style scoped>
/* Centering and styling are done via Bootstrap classes */
</style>
