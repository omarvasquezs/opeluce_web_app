<template>
  <div class="opeluce-change-password">
    <div class="container py-4">
      <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">
          <div class="opeluce-change-password-card">
            <!-- Header -->
            <div class="opeluce-card-header">
              <div class="opeluce-header-icon">
                <i class="fas fa-key"></i>
              </div>
              <h2 class="opeluce-card-title">Cambiar Contraseña</h2>
              <p class="opeluce-card-subtitle">
                Actualiza tu contraseña para mantener tu cuenta segura
              </p>
            </div>

            <!-- Alert Messages -->
            <div v-if="error" class="alert alert-danger" role="alert">
              <i class="fas fa-exclamation-triangle me-2"></i>
              {{ error }}
            </div>

            <div v-if="success" class="alert alert-success" role="alert">
              <i class="fas fa-check-circle me-2"></i>
              {{ success }}
            </div>

            <!-- Form -->
            <form @submit.prevent="changePassword" class="opeluce-form">
              <!-- Current Password -->
              <div class="mb-4">
                <label for="currentPassword" class="form-label opeluce-label">
                  <i class="fas fa-lock me-2"></i>Contraseña Actual
                </label>
                <div class="opeluce-password-field">
                  <input 
                    v-model="form.currentPassword" 
                    :type="showCurrentPassword ? 'text' : 'password'"
                    id="currentPassword" 
                    class="form-control opeluce-input" 
                    placeholder="Ingresa tu contraseña actual"
                    required 
                  />
                  <button 
                    type="button" 
                    class="opeluce-password-toggle"
                    @click="showCurrentPassword = !showCurrentPassword"
                  >
                    <i :class="['fas', showCurrentPassword ? 'fa-eye-slash' : 'fa-eye']"></i>
                  </button>
                </div>
              </div>

              <!-- New Password -->
              <div class="mb-4">
                <label for="newPassword" class="form-label opeluce-label">
                  <i class="fas fa-key me-2"></i>Nueva Contraseña
                </label>
                <div class="opeluce-password-field">
                  <input 
                    v-model="form.newPassword" 
                    :type="showNewPassword ? 'text' : 'password'"
                    id="newPassword" 
                    class="form-control opeluce-input" 
                    placeholder="Ingresa tu nueva contraseña"
                    minlength="8"
                    required 
                  />
                  <button 
                    type="button" 
                    class="opeluce-password-toggle"
                    @click="showNewPassword = !showNewPassword"
                  >
                    <i :class="['fas', showNewPassword ? 'fa-eye-slash' : 'fa-eye']"></i>
                  </button>
                </div>
                <small class="form-text text-muted">
                  <i class="fas fa-info-circle me-1"></i>
                  Mínimo 8 caracteres
                </small>
              </div>

              <!-- Confirm New Password -->
              <div class="mb-4">
                <label for="confirmPassword" class="form-label opeluce-label">
                  <i class="fas fa-shield-alt me-2"></i>Confirmar Nueva Contraseña
                </label>
                <div class="opeluce-password-field">
                  <input 
                    v-model="form.confirmPassword" 
                    :type="showConfirmPassword ? 'text' : 'password'"
                    id="confirmPassword" 
                    class="form-control opeluce-input" 
                    placeholder="Confirma tu nueva contraseña"
                    minlength="8"
                    required 
                  />
                  <button 
                    type="button" 
                    class="opeluce-password-toggle"
                    @click="showConfirmPassword = !showConfirmPassword"
                  >
                    <i :class="['fas', showConfirmPassword ? 'fa-eye-slash' : 'fa-eye']"></i>
                  </button>
                </div>
                <div v-if="form.newPassword && form.confirmPassword && !passwordsMatch" class="text-danger mt-1">
                  <i class="fas fa-times me-1"></i>
                  Las contraseñas no coinciden
                </div>
                <div v-if="form.newPassword && form.confirmPassword && passwordsMatch" class="text-success mt-1">
                  <i class="fas fa-check me-1"></i>
                  Las contraseñas coinciden
                </div>
              </div>

              <!-- Password Strength Indicator -->
              <div v-if="form.newPassword" class="mb-4">
                <label class="form-label opeluce-label">
                  <i class="fas fa-shield-alt me-2"></i>Fuerza de la Contraseña
                </label>
                <div class="opeluce-password-strength">
                  <div class="opeluce-strength-bar">
                    <div 
                      class="opeluce-strength-fill" 
                      :style="{ width: passwordStrength.percentage + '%' }"
                      :class="'strength-' + passwordStrength.level"
                    ></div>
                  </div>
                  <span :class="'strength-' + passwordStrength.level" class="opeluce-strength-text">
                    {{ passwordStrength.text }}
                  </span>
                </div>
              </div>

              <!-- Action Buttons -->
              <div class="opeluce-form-actions">
                <button 
                  type="button" 
                  class="btn btn-secondary opeluce-btn-secondary me-3"
                  @click="goBack"
                  :disabled="loading"
                >
                  <i class="fas fa-arrow-left me-2"></i>Cancelar
                </button>
                <button 
                  type="submit" 
                  class="btn btn-primary opeluce-btn-primary"
                  :disabled="loading || !passwordsMatch || !form.newPassword || !form.currentPassword"
                >
                  <div v-if="loading" class="spinner-border spinner-border-sm me-2" role="status">
                    <span class="visually-hidden">Cargando...</span>
                  </div>
                  <i v-else class="fas fa-save me-2"></i>
                  {{ loading ? 'Actualizando...' : 'Cambiar Contraseña' }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref, computed } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import { userStore } from '@/stores/userStore.js';

const router = useRouter();

const form = reactive({
  currentPassword: '',
  newPassword: '',
  confirmPassword: ''
});

const loading = ref(false);
const error = ref('');
const success = ref('');

const showCurrentPassword = ref(false);
const showNewPassword = ref(false);
const showConfirmPassword = ref(false);

// Computed properties
const passwordsMatch = computed(() => {
  return form.newPassword && form.confirmPassword && form.newPassword === form.confirmPassword;
});

const passwordStrength = computed(() => {
  const password = form.newPassword;
  if (!password) return { level: 'weak', text: '', percentage: 0 };

  let score = 0;
  let text = '';
  
  // Length check
  if (password.length >= 8) score += 1;
  if (password.length >= 12) score += 1;
  
  // Character variety checks
  if (/[a-z]/.test(password)) score += 1;
  if (/[A-Z]/.test(password)) score += 1;
  if (/[0-9]/.test(password)) score += 1;
  if (/[^A-Za-z0-9]/.test(password)) score += 1;

  if (score <= 2) {
    return { level: 'weak', text: 'Débil', percentage: 25 };
  } else if (score <= 4) {
    return { level: 'medium', text: 'Moderada', percentage: 60 };
  } else {
    return { level: 'strong', text: 'Fuerte', percentage: 100 };
  }
});

async function changePassword() {
  if (!passwordsMatch.value) {
    error.value = 'Las contraseñas no coinciden';
    return;
  }

  if (form.newPassword.length < 8) {
    error.value = 'La nueva contraseña debe tener al menos 8 caracteres';
    return;
  }

  loading.value = true;
  error.value = '';
  success.value = '';

  try {
    await axios.post('/api/change-password', {
      current_password: form.currentPassword,
      new_password: form.newPassword,
      new_password_confirmation: form.confirmPassword
    });

    success.value = 'Contraseña actualizada exitosamente';
    
    // Clear form
    form.currentPassword = '';
    form.newPassword = '';
    form.confirmPassword = '';

    // Redirect after success
    setTimeout(() => {
      router.push('/');
    }, 2000);

  } catch (err) {
    console.error('Error changing password:', err);
    
    if (err.response?.data) {
      if (err.response.data.errors) {
        const errorMessages = Object.values(err.response.data.errors).flat();
        error.value = errorMessages.join(' ');
      } else if (err.response.data.message) {
        error.value = err.response.data.message;
      } else {
        error.value = 'Error al cambiar la contraseña';
      }
    } else {
      error.value = 'Error de conexión. Por favor, intente nuevamente.';
    }
  } finally {
    loading.value = false;
  }
}

function goBack() {
  router.back();
}
</script>

<style scoped>
.opeluce-change-password {
  min-height: 100vh;
  background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
  padding: 2rem 0;
}

.opeluce-change-password-card {
  background: white;
  border-radius: 16px;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
  border: 1px solid #eaeaea;
  overflow: hidden;
}

.opeluce-card-header {
  background: linear-gradient(135deg, #009fe3 0%, #007bb5 100%);
  color: white;
  padding: 2.5rem 2rem;
  text-align: center;
}

.opeluce-header-icon {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.2);
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 1.5rem;
  font-size: 2rem;
}

.opeluce-card-title {
  font-size: 2rem;
  font-weight: 700;
  margin-bottom: 0.5rem;
  font-family: 'ProximaNovaSemibold', sans-serif;
}

.opeluce-card-subtitle {
  font-size: 1.1rem;
  opacity: 0.9;
  margin: 0;
}

.opeluce-form {
  padding: 2.5rem 2rem;
}

.opeluce-label {
  color: #2c3e50;
  font-weight: 600;
  font-family: 'ProximaNovaSemibold', sans-serif;
  font-size: 0.95rem;
  margin-bottom: 0.5rem;
}

.opeluce-password-field {
  position: relative;
}

.opeluce-input {
  border: 2px solid #e9ecef;
  border-radius: 10px;
  padding: 0.75rem 1rem;
  padding-right: 3rem;
  font-family: 'ProximaNovaSemibold', sans-serif;
  font-size: 1rem;
  transition: border-color 0.2s, box-shadow 0.2s;
}

.opeluce-input:focus {
  border-color: #009fe3;
  box-shadow: 0 0 0 0.15rem rgba(0, 159, 227, 0.15);
  outline: none;
}

.opeluce-password-toggle {
  position: absolute;
  right: 12px;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  color: #6c757d;
  cursor: pointer;
  padding: 0;
  width: 24px;
  height: 24px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: color 0.2s;
}

.opeluce-password-toggle:hover {
  color: #009fe3;
}

/* Password Strength Indicator */
.opeluce-password-strength {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.opeluce-strength-bar {
  flex: 1;
  height: 8px;
  background: #e9ecef;
  border-radius: 4px;
  overflow: hidden;
}

.opeluce-strength-fill {
  height: 100%;
  transition: width 0.3s ease, background-color 0.3s ease;
  border-radius: 4px;
}

.strength-weak .opeluce-strength-fill,
.strength-weak.opeluce-strength-text {
  background: #dc3545;
  color: #dc3545;
}

.strength-medium .opeluce-strength-fill,
.strength-medium.opeluce-strength-text {
  background: #ffc107;
  color: #ffc107;
}

.strength-strong .opeluce-strength-fill,
.strength-strong.opeluce-strength-text {
  background: #28a745;
  color: #28a745;
}

.opeluce-strength-text {
  font-size: 0.875rem;
  font-weight: 600;
  min-width: 80px;
  text-align: right;
}

.opeluce-form-actions {
  display: flex;
  justify-content: center;
  align-items: center;
  margin-top: 2rem;
  flex-wrap: wrap;
  gap: 1rem;
}

.opeluce-btn-primary {
  background: linear-gradient(135deg, #009fe3, #007bb5);
  border: none;
  font-weight: 600;
  border-radius: 10px;
  padding: 0.75rem 2rem;
  font-family: 'ProximaNovaSemibold', sans-serif;
  transition: all 0.2s;
  min-width: 180px;
}

.opeluce-btn-primary:hover:not(:disabled) {
  transform: translateY(-1px);
  box-shadow: 0 6px 20px rgba(0, 159, 227, 0.3);
}

.opeluce-btn-primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.opeluce-btn-secondary {
  background: #6c757d;
  border: none;
  font-weight: 600;
  border-radius: 10px;
  padding: 0.75rem 1.5rem;
  font-family: 'ProximaNovaSemibold', sans-serif;
  transition: all 0.2s;
  color: white;
}

.opeluce-btn-secondary:hover {
  background: #5a6268;
  transform: translateY(-1px);
  color: white;
}

.alert {
  border-radius: 10px;
  border: none;
  font-family: 'ProximaNovaSemibold', sans-serif;
  margin-bottom: 1.5rem;
}

/* Responsive Design */
@media (max-width: 768px) {
  .opeluce-change-password {
    padding: 1rem 0;
  }
  
  .opeluce-card-header {
    padding: 2rem 1.5rem;
  }
  
  .opeluce-card-title {
    font-size: 1.5rem;
  }
  
  .opeluce-form {
    padding: 2rem 1.5rem;
  }
  
  .opeluce-form-actions {
    flex-direction: column;
  }
  
  .opeluce-btn-primary,
  .opeluce-btn-secondary {
    width: 100%;
    margin: 0;
  }
  
  .opeluce-password-strength {
    flex-direction: column;
    align-items: stretch;
    gap: 0.5rem;
  }
  
  .opeluce-strength-text {
    text-align: left;
  }
}
</style>
