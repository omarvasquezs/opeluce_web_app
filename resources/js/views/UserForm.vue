<template>
  <div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 class="opeluce-page-title">
        <i class="fas fa-user-edit me-3"></i>
        {{ isEdit ? 'Editar Usuario' : 'Crear Usuario' }}
      </h2>
      <router-link to="/users" class="btn btn-secondary opeluce-btn-secondary">
        <i class="fas fa-arrow-left me-2"></i>Volver a Usuarios
      </router-link>
    </div>

    <div class="opeluce-card">
      <div class="card-body p-4">
        <form @submit.prevent="submitForm">
          <div class="row g-4">
            <div class="col-md-6">
              <label for="name" class="form-label opeluce-label">
                <i class="fas fa-user me-2"></i>Nombre Completo
              </label>
              <input 
                v-model="form.name" 
                type="text" 
                id="name" 
                class="form-control opeluce-input" 
                placeholder="Ingrese el nombre completo"
                required 
              />
            </div>
            
            <div class="col-md-6">
              <label for="username" class="form-label opeluce-label">
                <i class="fas fa-at me-2"></i>Nombre de Usuario
              </label>
              <input 
                v-model="form.username" 
                type="text" 
                id="username" 
                class="form-control opeluce-input" 
                placeholder="Ingrese el nombre de usuario"
                required 
              />
            </div>
            
            <div class="col-md-6">
              <label for="email" class="form-label opeluce-label">
                <i class="fas fa-envelope me-2"></i>Correo Electrónico
              </label>
              <input 
                v-model="form.email" 
                type="email" 
                id="email" 
                class="form-control opeluce-input" 
                placeholder="usuario@ejemplo.com"
                required 
              />
            </div>
            
            <div class="col-md-6">
              <label for="password" class="form-label opeluce-label">
                <i class="fas fa-lock me-2"></i>Contraseña 
                <span v-if="isEdit" class="text-muted small">(dejar en blanco para mantener actual)</span>
              </label>
              <input 
                v-model="form.password" 
                type="password" 
                id="password" 
                class="form-control opeluce-input" 
                placeholder="Ingrese la contraseña"
                :required="!isEdit" 
              />
            </div>
            
            <div v-if="error" class="col-12">
              <div class="alert alert-danger opeluce-alert" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                {{ error }}
              </div>
            </div>
            
            <div class="col-12">
              <div class="d-flex gap-3">
                <button type="submit" class="btn btn-primary opeluce-btn" :disabled="loading">
                  <i :class="`fas me-2 ${loading ? 'fa-spinner fa-spin' : (isEdit ? 'fa-save' : 'fa-plus')}`"></i>
                  {{ loading ? 'Guardando...' : (isEdit ? 'Actualizar' : 'Crear') }} Usuario
                </button>
                <router-link to="/users" class="btn btn-outline-secondary">
                  <i class="fas fa-times me-2"></i>Cancelar
                </router-link>
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref, onMounted, computed } from 'vue';
import axios from 'axios';
import { useRouter } from 'vue-router';

const router = useRouter();
const props = defineProps(['id']);

const form = reactive({
  name: '',
  username: '',
  email: '',
  password: '',
});

const error = ref('');
const loading = ref(false);

const isEdit = computed(() => !!props.id);

async function loadUser() {
  if (isEdit.value) {
    loading.value = true;
    try {
      const response = await axios.get(`/api/users/${props.id}`);
      const user = response.data;
      form.name = user.name;
      form.username = user.username;
      form.email = user.email;
      // Don't load password for security
    } catch (err) {
      console.error('Error loading user:', err);
      error.value = 'Error al cargar el usuario.';
    } finally {
      loading.value = false;
    }
  }
}

async function submitForm() {
  error.value = '';
  loading.value = true;
  
  try {
    // Prepare form data, excluding empty password for edits
    const formData = { ...form };
    if (isEdit.value && !formData.password) {
      delete formData.password;
    }

    if (isEdit.value) {
      await axios.put(`/api/users/${props.id}`, formData);
    } else {
      await axios.post('/api/users', formData);
    }
    
    router.push('/users');
  } catch (e) {
    console.error('Error saving user:', e);
    
    if (e.response && e.response.data) {
      if (e.response.data.errors) {
        // Handle validation errors
        const errorMessages = Object.values(e.response.data.errors).flat();
        error.value = errorMessages.join(' ');
      } else if (e.response.data.message) {
        error.value = e.response.data.message;
      } else {
        error.value = 'Error al guardar el usuario.';
      }
    } else {
      error.value = 'Error de conexión. Por favor, intente nuevamente.';
    }
  } finally {
    loading.value = false;
  }
}

onMounted(() => {
  loadUser();
});
</script>

<style scoped>
.opeluce-page-title {
  color: #626161;
  font-family: 'ProximaNovaSemibold', sans-serif;
  font-weight: 600;
  margin: 0;
}

.opeluce-btn {
  background: #009fe3;
  border: none;
  font-weight: 600;
  border-radius: 8px;
  padding: 0.6rem 1.2rem;
  transition: all 0.2s;
}

.opeluce-btn:hover:not(:disabled) {
  background: #007bb5;
  transform: translateY(-1px);
}

.opeluce-btn:disabled {
  opacity: 0.6;
}

.opeluce-btn-secondary {
  background: #6c757d;
  border: none;
  color: white;
  font-weight: 600;
  border-radius: 8px;
  padding: 0.6rem 1.2rem;
  transition: all 0.2s;
  text-decoration: none;
}

.opeluce-btn-secondary:hover {
  background: #5a6268;
  color: white;
  text-decoration: none;
}

.opeluce-card {
  background: #fff;
  border-radius: 12px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
  border: 1px solid #eaeaea;
  overflow: hidden;
}

.opeluce-label {
  color: #626161;
  font-weight: 600;
  font-family: 'ProximaNovaSemibold', sans-serif;
  margin-bottom: 0.5rem;
}

.opeluce-input {
  border: 2px solid #eaeaea;
  border-radius: 8px;
  padding: 0.75rem 1rem;
  font-family: 'ProximaNovaSemibold', sans-serif;
  transition: border-color 0.2s, box-shadow 0.2s;
}

.opeluce-input:focus {
  border-color: #009fe3;
  box-shadow: 0 0 0 0.2rem rgba(0, 159, 227, 0.25);
}

.opeluce-alert {
  border-radius: 8px;
  border: none;
  font-family: 'ProximaNovaSemibold', sans-serif;
}

.gap-3 {
  gap: 1rem;
}
</style>
