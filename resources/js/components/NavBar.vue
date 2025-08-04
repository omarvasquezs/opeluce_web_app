<template>
  <nav class="opeluce-navbar shadow-sm">
    <div class="container d-flex align-items-center justify-content-between py-2">
      <div class="d-flex align-items-center">
        <router-link to="/">
          <img :src="baseUrl + '/images/logo-pages.svg'" alt="Opeluce Logo" class="opeluce-navbar-logo me-3" />
        </router-link>
      </div>
      <div class="flex-grow-1 d-flex justify-content-center">
        <ul class="nav">
          <!-- Inicio removed, logo is now the home link -->
          <li class="nav-item" v-if="isAdmin">
            <router-link class="nav-link opeluce-navbar-link" to="/users">
              <i class="fas fa-users me-2"></i>ADM. USUARIOS
            </router-link>
          </li>
          <li class="nav-item">
            <router-link class="nav-link opeluce-navbar-link" to="/devices">
              <i class="fas fa-laptop me-2"></i>ADM. DISPOSITIVOS
            </router-link>
          </li>
        </ul>
      </div>
      <div class="d-flex align-items-center position-relative">
        <div class="opeluce-user-menu" @mouseenter="menuOpen = true" @mouseleave="menuOpen = false">
          <span class="opeluce-username">
            <i class="fa fa-user me-2" aria-hidden="true"></i>
            <span class="opeluce-username-text">{{ username }}</span>
            <i class="fas fa-caret-down ms-1"></i>
          </span>
          <div v-if="menuOpen" class="opeluce-user-submenu">
            <button class="opeluce-user-action" @click="handleChangePassword">
              <i class="fas fa-key me-2"></i>CAMBIAR CONTRASEÑA
            </button>
            <button class="opeluce-user-action" @click="handleLogout">
              <i class="fas fa-sign-out-alt me-2"></i>SALIR DEL SISTEMA
            </button>
          </div>
        </div>
      </div>
    </div>
  </nav>
</template>

<script>
import axios from 'axios';
import { userStore } from '@/stores/userStore.js';

export default {
  data() {
    return {
      baseUrl: window.location.origin,
      menuOpen: false
    };
  },
  computed: {
    username() {
      return userStore.user?.username || 'Usuario';
    },
    isAdmin() {
      return userStore.isAdmin;
    }
  },
  methods: {
    async handleLogout() {
      try {
        await axios.post('/api/logout');
      } catch (error) {
        // Optionally handle errors
        console.error('Logout error:', error);
      }
      
      // Clear authentication data using the store
      userStore.clearUser();
      delete axios.defaults.headers.common['Authorization'];
      
      // Redirect to login
      window.location.href = '/login';
    },
    handleChangePassword() {
      this.$router.push('/change-password');
    },
    async refreshUserData() {
      await userStore.refreshUser();
    }
  },
  // Auto-refresh user data when component mounts
  mounted() {
    // Refresh user data from server to ensure it's up to date
    userStore.refreshUser();
  }
};
</script>

<style scoped>
/* User menu styles */
/* User menu styles */
.opeluce-user-menu {
  position: relative;
  cursor: pointer;
  user-select: none;
  display: flex;
  flex-direction: column;
  align-items: center;
  height: 70px;
  justify-content: center;
}
.opeluce-username {
  display: flex;
  align-items: center;
  font-weight: 600;
  color: #626161;
  font-size: 17px;
  padding: 0.5rem 1.2rem;
  border-radius: 20px;
  background: #fafdff;
  transition: background 0.2s;
  margin-bottom: 0;
  height: 100%;
}
.opeluce-username-text {
  font-family: 'ProximaNovaSemibold', sans-serif;
  font-size: 17px;
  letter-spacing: 0.5px;
}
.opeluce-user-menu:hover .opeluce-username {
  background: #eaf3fb;
}
/* Only one .opeluce-user-submenu style block, improved alignment */
.opeluce-user-submenu {
  position: absolute;
  top: 85%;
  left: 50%;
  transform: translateX(-50%);
  min-width: 220px;
  background: #fff;
  border: 1px solid #eaeaea;
  border-radius: 12px;
  box-shadow: 0 6px 24px rgba(0,0,0,0.10);
  z-index: 999;
  padding: 0.5rem 0.3rem;
  display: flex;
  flex-direction: column;
  align-items: center;
}
/* Submenu actions: smaller, normal weight, improved alignment */
.opeluce-user-action {
  background: none;
  border: none;
  color: #626161;
  font-family: 'ProximaNovaSemibold', sans-serif;
  font-size: 15px;
  padding: 0.6rem 1.1rem;
  text-align: left;
  cursor: pointer;
  border-radius: 7px;
  transition: background 0.2s, color 0.2s;
  font-weight: 400;
  letter-spacing: 0.2px;
  margin-bottom: 0.15rem;
  width: 100%;
  box-sizing: border-box;
  display: block;
}
.opeluce-user-action:last-child {
  margin-bottom: 0;
}
.opeluce-user-action:hover {
  background: #eaf3fb;
  color: #009fe3;
}
.opeluce-user-submenu {
  position: absolute;
  top: 85%;
  left: 50%;
  transform: translateX(-50%);
  min-width: 220px;
  background: #fff;
  border: 1px solid #eaeaea;
  border-radius: 12px;
  box-shadow: 0 6px 24px rgba(0,0,0,0.10);
  z-index: 999;
  padding: 0.5rem 0.3rem;
  display: flex;
  flex-direction: column;
  align-items: stretch;
  align-items: center;
}
.opeluce-navbar {
  background: #fff;
  border-bottom: 1px solid #eaeaea;
  min-height: 70px;
  font-family: 'ProximaNovaSemibold', sans-serif;
  position: sticky;
  top: 0;
  z-index: 101;
}

.opeluce-navbar-logo {
  height: 70px;
  width: auto;
  display: block;
  margin-top: 8px;
  margin-bottom: 0;
}

.opeluce-navbar-link {
  color: #626161;
  font-size: 18px;
  font-weight: 400;
  margin: 0 1.2rem;
  transition: color 0.2s;
  position: relative;
  text-align: center;
}

.opeluce-navbar-link.router-link-exact-active,
.opeluce-navbar-link:hover {
  color: #009fe3;
}

.opeluce-navbar-link::after {
  content: '';
  display: block;
  width: 0;
  height: 3px;
  background: #009fe3;
  margin: 8px auto 0 auto;
  border-radius: 2px;
  transition: width 0.25s;
}

.opeluce-navbar-link.router-link-exact-active::after,
.opeluce-navbar-link:hover::after {
  width: 70%;
}

.btn-primary {
  background: #009fe3;
  border: none;
  font-weight: 600;
}

.btn-primary:hover {
  background: #007bb5;
}
</style>
