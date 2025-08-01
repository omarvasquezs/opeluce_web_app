<template>
  <nav class="opeluce-navbar shadow-sm">
    <div class="container d-flex align-items-center justify-content-between py-2">
      <div class="d-flex align-items-center">
        <img :src="baseUrl + '/images/logo-pages.svg'" alt="Opeluce Logo" class="opeluce-navbar-logo me-3" />
      </div>
      <div class="flex-grow-1 d-flex justify-content-center">
        <ul class="nav">
          <li class="nav-item">
            <router-link class="nav-link opeluce-navbar-link" to="/">Inicio</router-link>
          </li>
          <li class="nav-item">
            <router-link class="nav-link opeluce-navbar-link" to="/users">ADM. USUARIOS</router-link>
          </li>
        </ul>
      </div>
      <div class="d-flex align-items-center">
        <button class="btn btn-primary rounded-pill px-4" @click="handleLogout">Cerrar Sesión</button>
      </div>
    </div>
  </nav>
</template>

<script>
import axios from 'axios';

export default {
  data() {
    return {
      baseUrl: window.location.origin,
      username: ''
    };
  },
  mounted() {
    const storedUser = localStorage.getItem('user');
    if (storedUser) {
      const userObj = JSON.parse(storedUser);
      this.username = userObj.username;
    }
  },
  methods: {
    async handleLogout() {
      try {
        await axios.post('/api/logout');
      } catch (error) {
        // Optionally handle errors
      }
      localStorage.removeItem('user');
      window.location.href = '/login';
    }
  }
};
</script>

<style scoped>
.opeluce-navbar {
  background: #fff;
  border-bottom: 1px solid #eaeaea;
  min-height: 70px;
  font-family: 'Montserrat', 'Arial', sans-serif;
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
  color: #444;
  font-size: 18px;
  font-weight: bold;
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
