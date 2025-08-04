<!--
  LoginForm.vue

  Description:
  -------------
  This Vue component renders a styled login form for the Opeluce management system.
  It features a two-column layout with a logo and a form card, including username and password fields.
  The background uses a blended image with a blue overlay for a modern look.

  Features:
  ---------
  - Responsive, visually appealing login card with Opeluce branding.
  - Username input automatically transforms input to uppercase.
  - Password input is masked for security.
  - Loading state disables inputs and shows a loading message on the button.
  - Error messages are displayed if login fails.
  - On successful login, user data is stored in localStorage and the user is redirected to the home page.
  - Uses Axios for API requests.

  Props:
  ------
  None

  Data:
  -----
  - baseUrl: String - The base URL of the application, used for logo image source.
  - username: String - The user's username (converted to uppercase).
  - password: String - The user's password.
  - errorMessage: String - Stores any error message to display on login failure.
  - isLoading: Boolean - Indicates if the login request is in progress.

  Methods:
  --------
  - login(): Handles form submission, sends credentials to the backend, manages loading and error states, and redirects on success.

  Usage:
  ------
  <login-form></login-form>

  Styling:
  --------
  - Scoped CSS provides a modern, branded look with responsive layout, gradients, and error highlighting.
  - The background image and overlay are handled with pseudo-elements for separation from content.

  Notes:
  ------
  - The login API endpoint is expected at '/api/login'.
  - Error handling displays backend error messages if available.
  - The username field enforces uppercase input both visually and in the data model.
-->
<template>
  <div class="opeluce-login-bg">
    <div class="opeluce-login-wrapper">
      <div class="opeluce-login-image">
        <img :src="baseUrl + '/images/logo-pages.svg'" alt="Opeluce Logo" class="opeluce-logo-left" />
      </div>
      <div class="opeluce-login-card">
        <div class="opeluce-login-header">
          <h2>Sistema de Gestion</h2>
          <p>Lensometro y Auto-keratorefractometro</p>
        </div>
        <form @submit.prevent="login" class="opeluce-login-form">
          <div class="opeluce-form-group">
            <label for="username">Usuario</label>
            <input id="username" v-model="username" @input="username = $event.target.value.toUpperCase()" type="text"
              required placeholder="Ingrese su usuario" :disabled="isLoading" class="uppercase-input" />
          </div>
          <div class="opeluce-form-group">
            <label for="password">Contraseña</label>
            <input id="password" v-model="password" type="password" required placeholder="********"
              :disabled="isLoading" />
          </div>
          <div class="opeluce-form-actions">
            <button type="submit" :disabled="isLoading" class="opeluce-login-btn">
              <span v-if="isLoading">Iniciando sesión...</span>
              <span v-else>Iniciar Sesión</span>
            </button>
          </div>
          <div v-if="errorMessage" class="opeluce-error-message">
            <span>{{ errorMessage }}</span>
            <button type="button" class="opeluce-error-close" @click="errorMessage = ''" aria-label="Cerrar">×</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import { userStore } from '@/stores/userStore.js';

const baseUrl = window.location.origin;

export default {
  name: 'LoginForm',
  data() {
    return {
      baseUrl,
      username: '',
      password: '',
      errorMessage: '',
      isLoading: false
    };
  },
  methods: {
    async login() {
      this.isLoading = true;
      this.errorMessage = '';
      try {
        const response = await axios.post('/api/login', {
          username: this.username,
          password: this.password
        });
        
        // Store user data and token using the user store
        userStore.setUser(response.data.user, response.data.token);
        
        // Set the token for future requests
        axios.defaults.headers.common['Authorization'] = `Bearer ${response.data.token}`;
        
        // Redirect to home page
        window.location.href = '/';
      } catch (error) {
        this.errorMessage = error.response?.data?.error || error.response?.data?.message || 'Error al iniciar sesión';
      } finally {
        this.isLoading = false;
      }
    }
  }
};
</script>

<style scoped>
.uppercase-input {
  text-transform: uppercase;
}

/* Background and layout */
.opeluce-login-bg {
  min-height: 100vh;
  width: 100vw;
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
}

/* Use a single background image with a gradient mask to blend into white */
.opeluce-login-bg::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  z-index: 0;
  background:
    linear-gradient(to right, rgba(255, 255, 255, 0) 60%, #fff 100%),
    url('https://www.opeluce.com.pe/blog/wp-content/uploads/2025/06/Biometria-ocular-Opeluce-2048x1365.jpg') center center / cover no-repeat;
  background-blend-mode: normal;
}

.opeluce-login-bg::after {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  z-index: 1;
  background: rgba(0, 123, 183, 0.45);
  /* blue overlay covers all */
  pointer-events: none;
}

.opeluce-login-wrapper {
  position: relative;
  z-index: 2;
}

.opeluce-login-wrapper {
  position: relative;
  z-index: 2;
  /* ...existing code... */
}

.opeluce-login-wrapper {
  display: flex;
  background: #fff;
  border-radius: 18px;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.12);
  overflow: hidden;
  max-width: 900px;
  width: 100%;
  min-height: 480px;
}

.opeluce-login-image {
  background: #eaf3fb;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 45%;
  min-width: 320px;
  padding: 2rem 1rem;
}

.opeluce-logo-left {
  max-width: 220px;
  width: 100%;
  height: auto;
  display: block;
  margin: 0 auto;
  filter: drop-shadow(0 2px 12px rgba(0, 0, 0, 0.08));
}

.opeluce-login-card {
  flex: 1;
  display: flex;
  flex-direction: column;
  justify-content: center;
  padding: 2.5rem 2rem;
}

.opeluce-login-header {
  text-align: center;
  margin-bottom: 2rem;
}

.opeluce-logo {
  max-width: 200px;
  height: auto;
  margin-bottom: 1.5rem;
}

.opeluce-login-header h2 {
  color: #1a3365;
  font-size: 2rem;
  font-weight: 700;
  margin-bottom: 0.5rem;
}

.opeluce-login-header p {
  color: #3b5998;
  font-size: 1.1rem;
  margin-bottom: 0;
}

.opeluce-login-form {
  width: 100%;
}

.opeluce-form-group {
  margin-bottom: 1.5rem;
}

.opeluce-form-group label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 600;
  color: #1a3365;
}

.opeluce-form-group input {
  width: 100%;
  padding: 0.75rem;
  border: 1.5px solid #b5c9e2;
  border-radius: 6px;
  font-size: 1rem;
  transition: border-color 0.3s;
  background: #fafdff;
}

.opeluce-form-group input:focus {
  outline: none;
  border-color: #3b8beb;
}

.opeluce-form-group input:disabled {
  background-color: #f5f5f5;
  cursor: not-allowed;
}

.opeluce-login-btn {
  width: 100%;
  padding: 0.75rem;
  background: linear-gradient(90deg, #3b8beb 0%, #1a3365 100%);
  color: white;
  border: none;
  border-radius: 6px;
  font-size: 1.1rem;
  font-weight: bold;
  cursor: pointer;
  transition: background 0.2s, box-shadow 0.2s;
  box-shadow: 0 2px 8px rgba(59, 139, 235, 0.08);
}

.opeluce-login-btn:hover:not(:disabled) {
  background: linear-gradient(90deg, #1a3365 0%, #3b8beb 100%);
}

.opeluce-login-btn:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.opeluce-error-message {
  margin-top: 1rem;
  padding: 0.75rem 2.5rem 0.75rem 0.75rem;
  background-color: #fee;
  color: #c33;
  border-radius: 6px;
  text-align: left;
  font-size: 0.95rem;
  position: relative;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.opeluce-error-close {
  position: absolute;
  top: 8px;
  right: 12px;
  background: none;
  border: none;
  color: #c33;
  font-size: 1.2rem;
  font-weight: bold;
  cursor: pointer;
  padding: 0;
  line-height: 1;
}
</style>
