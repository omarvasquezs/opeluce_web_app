<template>
  <div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2>{{ isEdit ? 'Edit User' : 'Create User' }}</h2>
      <router-link to="/users" class="btn btn-secondary">Back to Users</router-link>
    </div>
    <form @submit.prevent="submitForm" class="row g-3">
      <div class="col-md-6">
        <label for="name" class="form-label">Name</label>
        <input v-model="form.name" type="text" id="name" class="form-control" required />
      </div>
      <div class="col-md-6">
        <label for="username" class="form-label">Username</label>
        <input v-model="form.username" type="text" id="username" class="form-control" required />
      </div>
      <div class="col-md-6">
        <label for="email" class="form-label">Email</label>
        <input v-model="form.email" type="email" id="email" class="form-control" required />
      </div>
      <div class="col-md-6">
        <label for="password" class="form-label">Password {{ isEdit ? '(leave blank to keep current)' : '' }}</label>
        <input v-model="form.password" type="password" id="password" class="form-control" :required="!isEdit" />
      </div>
      <div v-if="error" class="col-12">
        <div class="alert alert-danger">{{ error }}</div>
      </div>
      <div class="col-12">
        <button type="submit" class="btn btn-primary">{{ isEdit ? 'Update' : 'Create' }} User</button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { reactive, ref, onMounted, computed } from 'vue';
import axios from 'axios';
import { useRouter, useRoute } from 'vue-router';

const router = useRouter();
const route = useRoute();
const props = defineProps(['id']);

const form = reactive({
  name: '',
  username: '',
  email: '',
  password: '',
});
const error = ref('');

const isEdit = computed(() => !!props.id);

async function loadUser() {
  if (isEdit.value) {
    try {
      const response = await axios.get(`/api/users/${props.id}`);
      const user = response.data;
      form.name = user.name;
      form.username = user.username;
      form.email = user.email;
      // Don't load password for security
    } catch (error) {
      console.error('Error loading user:', error);
    }
  }
}

async function submitForm() {
  error.value = '';
  try {
    if (isEdit.value) {
      await axios.put(`/api/users/${props.id}`, form);
    } else {
      await axios.post('/api/users', form);
    }
    router.push('/users');
  } catch (e) {
    if (e.response && e.response.data && e.response.data.message) {
      error.value = e.response.data.message;
    } else {
      error.value = 'An error occurred while saving the user.';
    }
  }
}

onMounted(() => {
  loadUser();
});
</script>
