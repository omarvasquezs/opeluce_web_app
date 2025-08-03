<template>
  <div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 class="opeluce-page-title">
        <i class="fas fa-users me-3"></i>Administración de Usuarios
      </h2>
      <router-link to="/users/create" class="btn btn-primary opeluce-btn">
        <i class="fas fa-plus me-2"></i>Agregar Usuario
      </router-link>
    </div>

    <!-- Loading state -->
    <div v-if="loading" class="text-center py-5">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Cargando...</span>
      </div>
      <p class="mt-3 text-muted">Cargando usuarios...</p>
    </div>

    <!-- Error state -->
    <div v-else-if="error" class="alert alert-danger" role="alert">
      <i class="fas fa-exclamation-triangle me-2"></i>
      {{ error }}
    </div>

    <!-- Empty state -->
    <div v-else-if="users.length === 0" class="text-center py-5">
      <i class="fas fa-users fa-3x text-muted mb-3"></i>
      <h4 class="text-muted">No hay usuarios registrados</h4>
      <p class="text-muted">Comience agregando su primer usuario.</p>
      <router-link to="/users/create" class="btn btn-primary opeluce-btn">
        <i class="fas fa-plus me-2"></i>Agregar Primer Usuario
      </router-link>
    </div>

    <!-- Users table -->
    <div v-else class="opeluce-card">
      <div class="table-responsive">
        <table class="table table-hover opeluce-table">
          <thead class="opeluce-table-header">
            <tr>
              <th><i class="fas fa-user me-2"></i>Nombre</th>
              <th><i class="fas fa-envelope me-2"></i>Email</th>
              <th><i class="fas fa-at me-2"></i>Usuario</th>
              <th><i class="fas fa-calendar me-2"></i>Fecha de Creación</th>
              <th><i class="fas fa-cog me-2"></i>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="user in users" :key="user.id" class="opeluce-table-row">
              <td>
                <div class="fw-semibold">{{ user.name }}</div>
              </td>
              <td class="text-muted">{{ user.email }}</td>
              <td>
                <span class="opeluce-badge">{{ user.username }}</span>
              </td>
              <td class="text-muted">{{ formatDate(user.created_at) }}</td>
              <td>
                <div class="btn-group" role="group">
                  <router-link 
                    :to="`/users/${user.id}/edit`" 
                    class="btn btn-sm btn-outline-primary"
                    title="Editar usuario"
                  >
                    <i class="fas fa-edit"></i>
                  </router-link>
                  <button 
                    @click="deleteUser(user.id)" 
                    class="btn btn-sm btn-outline-danger"
                    title="Eliminar usuario"
                  >
                    <i class="fas fa-trash"></i>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const users = ref([]);
const loading = ref(false);
const error = ref('');

async function loadUsers() {
  loading.value = true;
  error.value = '';
  try {
    const response = await axios.get('/api/users');
    users.value = response.data;
    console.log('Users loaded:', response.data);
  } catch (err) {
    console.error('Error loading users:', err);
    error.value = 'Error al cargar los usuarios. Por favor, intente nuevamente.';
    if (err.response) {
      if (err.response.status === 401) {
        error.value = 'No autorizado. Por favor, inicie sesión nuevamente.';
      } else if (err.response.status === 403) {
        error.value = 'No tiene permisos para ver los usuarios.';
      }
    }
  } finally {
    loading.value = false;
  }
}

async function deleteUser(id) {
  if (confirm('¿Está seguro de que desea eliminar este usuario? Esta acción no se puede deshacer.')) {
    try {
      await axios.delete(`/api/users/${id}`);
      await loadUsers(); // Reload the list
      // Show success message
      alert('Usuario eliminado exitosamente.');
    } catch (err) {
      console.error('Error deleting user:', err);
      let errorMessage = 'Error al eliminar el usuario.';
      if (err.response && err.response.data && err.response.data.message) {
        errorMessage = err.response.data.message;
      }
      alert(errorMessage);
    }
  }
}

function formatDate(dateString) {
  if (!dateString) return 'N/A';
  
  try {
    const date = new Date(dateString);
    
    // Check if date is valid
    if (isNaN(date.getTime())) {
      return 'Fecha inválida';
    }
    
    return date.toLocaleDateString('es-ES', {
      year: 'numeric',
      month: 'long',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    });
  } catch (error) {
    console.error('Error formatting date:', error);
    return 'Fecha inválida';
  }
}

onMounted(() => {
  loadUsers();
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

.opeluce-btn:hover {
  background: #007bb5;
  transform: translateY(-1px);
}

.opeluce-card {
  background: #fff;
  border-radius: 12px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
  border: 1px solid #eaeaea;
  overflow: hidden;
}

.opeluce-table {
  margin: 0;
  font-family: 'ProximaNovaSemibold', sans-serif;
}

.opeluce-table-header {
  background: #f8f9fa;
  border-bottom: 2px solid #009fe3;
}

.opeluce-table-header th {
  color: #626161;
  font-weight: 600;
  font-size: 14px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  padding: 1rem 1.2rem;
  border: none;
}

.opeluce-table-row {
  transition: background-color 0.2s;
}

.opeluce-table-row:hover {
  background-color: #f8fffe;
}

.opeluce-table td {
  padding: 1rem 1.2rem;
  vertical-align: middle;
  border-color: #f0f0f0;
}

.opeluce-avatar {
  width: 40px;
  height: 40px;
  background: linear-gradient(135deg, #009fe3, #007bb5);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 14px;
}

.opeluce-badge {
  background: #e3f2fd;
  color: #1565c0;
  padding: 0.3rem 0.8rem;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 600;
  letter-spacing: 0.5px;
  text-transform: uppercase;
}

.btn-group .btn {
  margin: 0 2px;
  border-radius: 6px;
  transition: all 0.2s;
}

.btn-outline-primary:hover {
  background-color: #009fe3;
  border-color: #009fe3;
}

.btn-outline-danger:hover {
  background-color: #dc3545;
  border-color: #dc3545;
}

.spinner-border {
  width: 3rem;
  height: 3rem;
}

.alert {
  border-radius: 8px;
  border: none;
  font-family: 'ProximaNovaSemibold', sans-serif;
}
</style>
