<template>
  <div class="opeluce-dashboard">
    <!-- Hero Section -->
    <div class="opeluce-hero-section">
      <div class="container">
        <div class="row align-items-center">
          <div class="col-lg-8">
            <div class="opeluce-hero-content">
              <h1 class="opeluce-hero-title">
                <i class="fas fa-eye me-3"></i>
                Bienvenido a Opeluce S.A.C.
              </h1>
              <p class="opeluce-hero-subtitle">
                Sistema de Gestión Integral - Clínica de Ojos Especializada
              </p>
              <p class="opeluce-hero-description">
                Tecnología avanzada al servicio de la salud visual. Gestiona usuarios, dispositivos y operaciones con eficiencia y seguridad.
              </p>
            </div>
          </div>
          <div class="col-lg-4 text-center">
            <div class="opeluce-hero-image">
              <img :src="baseUrl + '/images/optometrista.jpg'" alt="Optometrista" class="img-fluid rounded-circle opeluce-hero-img" />
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Statistics Section -->
    <div class="opeluce-stats-section">
      <div class="container">
        <div class="row g-4">
          <!-- Total Users Card -->
          <div class="col-md-6 col-lg-3">
            <div class="opeluce-stat-card">
              <div class="opeluce-stat-icon bg-primary">
                <i class="fas fa-users"></i>
              </div>
              <div class="opeluce-stat-content">
                <h3 class="opeluce-stat-number">{{ stats.totalUsers }}</h3>
                <p class="opeluce-stat-label">Usuarios Totales</p>
              </div>
            </div>
          </div>

          <!-- Admin Users Card -->
          <div class="col-md-6 col-lg-3">
            <div class="opeluce-stat-card">
              <div class="opeluce-stat-icon bg-danger">
                <i class="fas fa-shield-alt"></i>
              </div>
              <div class="opeluce-stat-content">
                <h3 class="opeluce-stat-number">{{ stats.adminUsers }}</h3>
                <p class="opeluce-stat-label">Administradores</p>
              </div>
            </div>
          </div>

          <!-- Regular Users Card -->
          <div class="col-md-6 col-lg-3">
            <div class="opeluce-stat-card">
              <div class="opeluce-stat-icon bg-success">
                <i class="fas fa-user"></i>
              </div>
              <div class="opeluce-stat-content">
                <h3 class="opeluce-stat-number">{{ stats.regularUsers }}</h3>
                <p class="opeluce-stat-label">Usuarios Regulares</p>
              </div>
            </div>
          </div>

          <!-- System Status Card -->
          <div class="col-md-6 col-lg-3">
            <div class="opeluce-stat-card">
              <div class="opeluce-stat-icon bg-info">
                <i class="fas fa-heartbeat"></i>
              </div>
              <div class="opeluce-stat-content">
                <h3 class="opeluce-stat-number">{{ systemStatus }}</h3>
                <p class="opeluce-stat-label">Estado del Sistema</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Quick Actions Section -->
    <div class="opeluce-actions-section">
      <div class="container">
        <div class="row">
          <div class="col-12">
            <h2 class="opeluce-section-title">
              <i class="fas fa-bolt me-2"></i>Acciones Rápidas
            </h2>
          </div>
        </div>
        <div class="row g-4">
          <!-- User Management Card -->
          <div class="col-md-6 col-lg-4" v-if="isAdmin">
            <div class="opeluce-action-card">
              <div class="opeluce-action-icon">
                <i class="fas fa-users-cog"></i>
              </div>
              <h4 class="opeluce-action-title">Gestión de Usuarios</h4>
              <p class="opeluce-action-description">
                Administra usuarios del sistema, roles y permisos de acceso.
              </p>
              <router-link to="/users" class="btn btn-primary opeluce-action-btn">
                <i class="fas fa-arrow-right me-2"></i>Administrar Usuarios
              </router-link>
            </div>
          </div>

          <!-- Device Management Card -->
          <div class="col-md-6 col-lg-4">
            <div class="opeluce-action-card">
              <div class="opeluce-action-icon">
                <i class="fas fa-laptop-medical"></i>
              </div>
              <h4 class="opeluce-action-title">Gestión de Dispositivos</h4>
              <p class="opeluce-action-description">
                Control y monitoreo de equipos médicos y tecnológicos.
              </p>
              <router-link to="/devices" class="btn btn-info opeluce-action-btn">
                <i class="fas fa-arrow-right me-2"></i>Administrar Dispositivos
              </router-link>
            </div>
          </div>

          <!-- Profile Settings Card -->
          <div class="col-md-6 col-lg-4">
            <div class="opeluce-action-card">
              <div class="opeluce-action-icon">
                <i class="fas fa-user-cog"></i>
              </div>
              <h4 class="opeluce-action-title">Mi Perfil</h4>
              <p class="opeluce-action-description">
                Actualiza tu información personal y configuraciones de cuenta.
              </p>
              <button @click="handleChangePassword" class="btn btn-secondary opeluce-action-btn">
                <i class="fas fa-key me-2"></i>Cambiar Contraseña
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- System Information Section -->
    <div class="opeluce-info-section">
      <div class="container">
        <div class="row">
          <div class="col-lg-8">
            <div class="opeluce-info-card">
              <h3 class="opeluce-info-title">
                <i class="fas fa-info-circle me-2"></i>Acerca del Sistema
              </h3>
              <div class="opeluce-info-content">
                <p>
                  <strong>Opeluce S.A.C.</strong> es un sistema integral de gestión diseñado específicamente 
                  para clínicas oftalmológicas modernas. Nuestro sistema proporciona:
                </p>
                <ul class="opeluce-feature-list">
                  <li><i class="fas fa-check me-2"></i>Gestión segura de usuarios con control de roles</li>
                  <li><i class="fas fa-check me-2"></i>Administración de dispositivos médicos</li>
                  <li><i class="fas fa-check me-2"></i>Interfaz intuitiva y responsive</li>
                  <li><i class="fas fa-check me-2"></i>Seguridad de datos avanzada</li>
                </ul>
              </div>
            </div>
          </div>
          <div class="col-lg-4">
            <div class="opeluce-user-card">
              <div class="opeluce-user-avatar">
                <i class="fas fa-user-circle"></i>
              </div>
              <h4 class="opeluce-user-name">{{ username }}</h4>
              <p class="opeluce-user-role">
                <i :class="['fas', 'me-1', isAdmin ? 'fa-shield-alt' : 'fa-user']"></i>
                {{ isAdmin ? 'Administrador' : 'Usuario' }}
              </p>
              <p class="opeluce-user-welcome">
                ¡Bienvenido de vuelta! Tu última sesión fue registrada con éxito.
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import { userStore } from '@/stores/userStore.js';

const router = useRouter();

const stats = ref({
  totalUsers: 0,
  adminUsers: 0,
  regularUsers: 0
});

const baseUrl = window.location.origin;

const username = computed(() => userStore.user?.name || 'Usuario');
const isAdmin = computed(() => userStore.isAdmin);
const systemStatus = ref('Activo');

async function loadStats() {
  if (isAdmin.value) {
    try {
      const response = await axios.get('/api/users');
      const users = response.data;
      
      stats.value.totalUsers = users.length;
      stats.value.adminUsers = users.filter(user => user.role === true).length;
      stats.value.regularUsers = users.filter(user => user.role === false).length;
    } catch (error) {
      console.error('Error loading stats:', error);
      // Set default values if we can't load stats
      stats.value = {
        totalUsers: '-',
        adminUsers: '-',
        regularUsers: '-'
      };
    }
  } else {
    // For non-admin users, don't show specific numbers
    stats.value = {
      totalUsers: 'N/A',
      adminUsers: 'N/A',
      regularUsers: 'N/A'
    };
  }
}

function handleChangePassword() {
  router.push('/change-password');
}

onMounted(() => {
  loadStats();
});
</script>

<style scoped>
.opeluce-dashboard {
  min-height: 100vh;
  background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
}

/* Hero Section */
.opeluce-hero-section {
  background: linear-gradient(135deg, #009fe3 0%, #007bb5 100%);
  color: white;
  padding: 4rem 0;
  margin-bottom: 3rem;
}

.opeluce-hero-title {
  font-size: 2.5rem;
  font-weight: 700;
  margin-bottom: 1rem;
  font-family: 'ProximaNovaSemibold', sans-serif;
}

.opeluce-hero-subtitle {
  font-size: 1.25rem;
  font-weight: 600;
  margin-bottom: 1rem;
  opacity: 0.9;
}

.opeluce-hero-description {
  font-size: 1.1rem;
  opacity: 0.8;
  line-height: 1.6;
}

.opeluce-hero-img {
  width: 200px;
  height: 200px;
  object-fit: cover;
  border: 4px solid rgba(255, 255, 255, 0.3);
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
}

/* Statistics Section */
.opeluce-stats-section {
  margin-bottom: 3rem;
}

.opeluce-stat-card {
  background: white;
  border-radius: 12px;
  padding: 2rem;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
  border: 1px solid #eaeaea;
  transition: transform 0.2s, box-shadow 0.2s;
  display: flex;
  align-items: center;
  gap: 1.5rem;
}

.opeluce-stat-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
}

.opeluce-stat-icon {
  width: 60px;
  height: 60px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  color: white;
}

.opeluce-stat-number {
  font-size: 2rem;
  font-weight: 700;
  margin: 0;
  color: #2c3e50;
  font-family: 'ProximaNovaSemibold', sans-serif;
}

.opeluce-stat-label {
  margin: 0;
  color: #6c757d;
  font-weight: 500;
  font-size: 0.9rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

/* Actions Section */
.opeluce-actions-section {
  margin-bottom: 3rem;
}

.opeluce-section-title {
  color: #2c3e50;
  font-weight: 700;
  margin-bottom: 2rem;
  font-family: 'ProximaNovaSemibold', sans-serif;
}

.opeluce-action-card {
  background: white;
  border-radius: 12px;
  padding: 2rem;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
  border: 1px solid #eaeaea;
  transition: transform 0.2s, box-shadow 0.2s;
  text-align: center;
  height: 100%;
}

.opeluce-action-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 28px rgba(0, 0, 0, 0.12);
}

.opeluce-action-icon {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  background: linear-gradient(135deg, #009fe3, #007bb5);
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 1.5rem;
  font-size: 2rem;
  color: white;
}

.opeluce-action-title {
  color: #2c3e50;
  font-weight: 600;
  margin-bottom: 1rem;
  font-family: 'ProximaNovaSemibold', sans-serif;
}

.opeluce-action-description {
  color: #6c757d;
  margin-bottom: 1.5rem;
  line-height: 1.6;
}

.opeluce-action-btn {
  font-weight: 600;
  border-radius: 8px;
  padding: 0.75rem 1.5rem;
  transition: all 0.2s;
  border: none;
  text-decoration: none;
}

.opeluce-action-btn:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

/* Info Section */
.opeluce-info-section {
  margin-bottom: 2rem;
}

.opeluce-info-card {
  background: white;
  border-radius: 12px;
  padding: 2rem;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
  border: 1px solid #eaeaea;
  height: 100%;
}

.opeluce-info-title {
  color: #2c3e50;
  font-weight: 600;
  margin-bottom: 1.5rem;
  font-family: 'ProximaNovaSemibold', sans-serif;
}

.opeluce-info-content {
  color: #495057;
  line-height: 1.6;
}

.opeluce-feature-list {
  list-style: none;
  padding: 0;
}

.opeluce-feature-list li {
  padding: 0.5rem 0;
  color: #495057;
}

.opeluce-feature-list i {
  color: #28a745;
}

/* User Card */
.opeluce-user-card {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border-radius: 12px;
  padding: 2rem;
  text-align: center;
  height: 100%;
}

.opeluce-user-avatar {
  font-size: 4rem;
  margin-bottom: 1rem;
  opacity: 0.9;
}

.opeluce-user-name {
  font-weight: 600;
  margin-bottom: 0.5rem;
  font-family: 'ProximaNovaSemibold', sans-serif;
}

.opeluce-user-role {
  margin-bottom: 1rem;
  opacity: 0.8;
  font-weight: 500;
}

.opeluce-user-welcome {
  opacity: 0.7;
  line-height: 1.5;
  margin: 0;
}

/* Responsive Design */
@media (max-width: 768px) {
  .opeluce-hero-title {
    font-size: 2rem;
  }
  
  .opeluce-hero-section {
    padding: 2rem 0;
  }
  
  .opeluce-stat-card {
    text-align: center;
    flex-direction: column;
  }
  
  .opeluce-stats-section,
  .opeluce-actions-section,
  .opeluce-info-section {
    margin-bottom: 2rem;
  }
}
</style>
