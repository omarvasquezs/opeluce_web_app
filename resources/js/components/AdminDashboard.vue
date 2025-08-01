<template>
  <div class="admin-dashboard">
    <!-- Top Navigation Bar -->
    <nav class="top-nav">
      <div class="nav-left">
        <h1>Sistema de Refractometría</h1>
      </div>
      <div class="nav-right">
        <span class="user-info">Bienvenido, {{ user.user.name }}</span>
        <button @click="logout" class="logout-btn">Cerrar Sesión</button>
      </div>
    </nav>

    <div class="dashboard-content">
      <!-- Sidebar Menu -->
      <aside class="sidebar">
        <nav class="sidebar-nav">
          <ul>
            <li>
              <a 
                href="#" 
                @click="setActiveComponent('HistoriaClinica')"
                :class="{ active: activeComponent === 'HistoriaClinica' }"
              >
                <i class="icon">📋</i>
                Historia Clínica
              </a>
            </li>
            <li>
              <a 
                href="#" 
                @click="setActiveComponent('AgudezaVisual')"
                :class="{ active: activeComponent === 'AgudezaVisual' }"
              >
                <i class="icon">👁️</i>
                Agudeza Visual
              </a>
            </li>
            <li>
              <a 
                href="#" 
                @click="setActiveComponent('AutoKeratometry')"
                :class="{ active: activeComponent === 'AutoKeratometry' }"
              >
                <i class="icon">🔍</i>
                Auto Keratometría
              </a>
            </li>
            <li>
              <a 
                href="#" 
                @click="setActiveComponent('Lensmeter')"
                :class="{ active: activeComponent === 'Lensmeter' }"
              >
                <i class="icon">🥽</i>
                Lensómetro
              </a>
            </li>
            <li>
              <a 
                href="#" 
                @click="setActiveComponent('Patients')"
                :class="{ active: activeComponent === 'Patients' }"
              >
                <i class="icon">👥</i>
                Pacientes
              </a>
            </li>
            <li>
              <a 
                href="#" 
                @click="setActiveComponent('Reports')"
                :class="{ active: activeComponent === 'Reports' }"
              >
                <i class="icon">📊</i>
                Reportes
              </a>
            </li>
          </ul>
        </nav>
      </aside>

      <!-- Main Content Area -->
      <main class="main-content">
        <component :is="currentComponent" />
      </main>
    </div>
  </div>
</template>

<script>
import HistoriaClinica from './modules/HistoriaClinica.vue';
import AgudezaVisual from './modules/AgudezaVisual.vue';
import AutoKeratometry from './modules/AutoKeratometry.vue';
import Lensmeter from './modules/Lensmeter.vue';
import Patients from './modules/Patients.vue';
import Reports from './modules/Reports.vue';

export default {
  name: 'AdminDashboard',
  props: {
    user: {
      type: Object,
      required: true
    }
  },
  components: {
    HistoriaClinica,
    AgudezaVisual,
    AutoKeratometry,
    Lensmeter,
    Patients,
    Reports
  },
  data() {
    return {
      activeComponent: 'HistoriaClinica'
    };
  },
  computed: {
    currentComponent() {
      return this.activeComponent;
    }
  },
  methods: {
    setActiveComponent(component) {
      this.activeComponent = component;
    },
    logout() {
      this.$emit('logout');
    }
  }
};
</script>

<style scoped>
.admin-dashboard {
  min-height: 100vh;
  background: #f5f5f5;
}

.top-nav {
  background: white;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  padding: 1rem 2rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  position: sticky;
  top: 0;
  z-index: 100;
}

.nav-left h1 {
  color: #333;
  margin: 0;
  font-size: 1.5rem;
}

.nav-right {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.user-info {
  color: #666;
  font-weight: 500;
}

.logout-btn {
  padding: 0.5rem 1rem;
  background: #dc3545;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 0.9rem;
  transition: background-color 0.3s;
}

.logout-btn:hover {
  background: #c82333;
}

.dashboard-content {
  display: flex;
  min-height: calc(100vh - 80px);
}

.sidebar {
  width: 250px;
  background: white;
  box-shadow: 2px 0 4px rgba(0,0,0,0.1);
}

.sidebar-nav ul {
  list-style: none;
  padding: 0;
  margin: 0;
}

.sidebar-nav li {
  border-bottom: 1px solid #eee;
}

.sidebar-nav a {
  display: flex;
  align-items: center;
  padding: 1rem 1.5rem;
  color: #333;
  text-decoration: none;
  transition: background-color 0.3s;
}

.sidebar-nav a:hover,
.sidebar-nav a.active {
  background: #667eea;
  color: white;
}

.sidebar-nav .icon {
  margin-right: 0.75rem;
  font-size: 1.2rem;
}

.main-content {
  flex: 1;
  padding: 2rem;
  background: #f5f5f5;
}
</style>
