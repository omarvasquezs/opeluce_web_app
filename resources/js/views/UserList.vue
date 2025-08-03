<template>
  <div class="container py-4 mb-5">
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
      <!-- Filters Row -->
      <div class="opeluce-filters p-3 border-bottom">
        <div class="row g-3">
          <div class="col-md-2">
            <label class="form-label opeluce-filter-label">Filtrar por Nombre</label>
            <input 
              v-model="filters.name" 
              type="text" 
              class="form-control opeluce-filter-input" 
              placeholder="Buscar por nombre..."
              @input="filters.name = $event.target.value.toUpperCase(); applyFilters()"
            />
          </div>
          <div class="col-md-3">
            <label class="form-label opeluce-filter-label">Filtrar por Email</label>
            <input 
              v-model="filters.email" 
              type="text" 
              class="form-control opeluce-filter-input" 
              placeholder="Buscar por email..."
              @input="filters.email = $event.target.value.toUpperCase(); applyFilters()"
            />
          </div>
          <div class="col-md-2">
            <label class="form-label opeluce-filter-label">Filtrar por Usuario</label>
            <input 
              v-model="filters.username" 
              type="text" 
              class="form-control opeluce-filter-input" 
              placeholder="Buscar usuario..."
              @input="filters.username = $event.target.value.toUpperCase(); applyFilters()"
            />
          </div>
          <div class="col-md-2">
            <label class="form-label opeluce-filter-label">Filtrar por Rol</label>
            <select 
              v-model="filters.role" 
              class="form-select opeluce-filter-input"
              @change="applyFilters"
            >
              <option value="">Todos los roles</option>
              <option value="admin">Administradores</option>
              <option value="user">Usuarios</option>
            </select>
          </div>
          <div class="col-md-2">
            <label class="form-label opeluce-filter-label">Fecha de Creación</label>
            <input 
              v-model="filters.createdAt" 
              type="date" 
              class="form-control opeluce-filter-input" 
              @change="applyFilters"
            />
          </div>
          <div class="col-md-1 d-flex align-items-end">
            <button 
              @click="clearFilters" 
              class="btn btn-outline-secondary w-100"
              title="Limpiar filtros"
            >
              <i class="fas fa-times"></i>
            </button>
          </div>
        </div>
      </div>

      <div class="table-responsive">
        <table class="table table-hover opeluce-table">
          <thead class="opeluce-table-header">
            <tr>
              <th><i class="fas fa-user me-2"></i>Nombre</th>
              <th><i class="fas fa-envelope me-2"></i>Email</th>
              <th><i class="fas fa-at me-2"></i>Usuario</th>
              <th><i class="fas fa-shield me-2"></i>Rol</th>
              <th><i class="fas fa-calendar me-2"></i>Fecha de Creación</th>
              <th class="text-center"><i class="fas fa-cog me-2"></i>Acciones</th>
            </tr>
          </thead>
                    <tbody>
            <tr v-if="loading">
              <td colspan="6" class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                  <span class="visually-hidden">Cargando...</span>
                </div>
              </td>
            </tr>
            <tr v-else-if="paginatedUsers.length === 0">
              <td colspan="6" class="text-center py-4 text-muted">
                <i class="fas fa-users fa-2x mb-2"></i>
                <div>No hay usuarios para mostrar</div>
              </td>
            </tr>
            <tr v-else v-for="user in paginatedUsers" :key="user.id">
              <td>{{ user.name }}</td>
              <td>{{ user.email }}</td>
              <td>{{ user.username }}</td>
              <td>
                <span 
                  :class="['badge', user.role ? 'bg-danger' : 'bg-primary']"
                  :title="user.role ? 'Usuario administrador' : 'Usuario normal'"
                >
                  <i :class="['fas', 'me-1', user.role ? 'fa-shield-alt' : 'fa-user']"></i>
                  {{ user.role ? 'Admin' : 'Usuario' }}
                </span>
              </td>
              <td>{{ formatDate(user.created_at) }}</td>
              <td class="text-center">
                <div class="d-flex gap-2 justify-content-center" role="group">
                  <router-link 
                    :to="`/admin/users/${user.id}/edit`" 
                    class="btn btn-outline-primary btn-sm opeluce-action-btn"
                    title="Editar usuario">
                    <i class="fas fa-edit"></i>
                  </router-link>
                  <button 
                    @click="deleteUser(user.id)" 
                    class="btn btn-outline-danger btn-sm opeluce-action-btn"
                    title="Eliminar usuario">
                    <i class="fas fa-trash"></i>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      
      <!-- Pagination -->
      <div v-if="totalPages > 1" class="opeluce-pagination p-3 border-top">
        <div class="d-flex justify-content-between align-items-center">
          <div class="opeluce-pagination-info">
            <span class="text-muted">
              Mostrando {{ startItem }} - {{ endItem }} de {{ totalItems }} usuarios
            </span>
          </div>
          <nav aria-label="Paginación de usuarios">
            <ul class="pagination pagination-sm mb-0">
              <li class="page-item" :class="{ disabled: currentPage === 1 }">
                <button 
                  class="page-link opeluce-page-link" 
                  @click="changePage(currentPage - 1)"
                  :disabled="currentPage === 1"
                >
                  <i class="fas fa-chevron-left"></i>
                </button>
              </li>
              
              <li 
                v-for="page in visiblePages" 
                :key="page"
                class="page-item"
                :class="{ active: page === currentPage }"
              >
                <button 
                  class="page-link opeluce-page-link"
                  @click="changePage(page)"
                  :class="{ 'opeluce-page-active': page === currentPage }"
                >
                  {{ page }}
                </button>
              </li>
              
              <li class="page-item" :class="{ disabled: currentPage === totalPages }">
                <button 
                  class="page-link opeluce-page-link" 
                  @click="changePage(currentPage + 1)"
                  :disabled="currentPage === totalPages"
                >
                  <i class="fas fa-chevron-right"></i>
                </button>
              </li>
            </ul>
          </nav>
          <div class="opeluce-items-per-page">
            <select 
              v-model="itemsPerPage" 
              class="form-select form-select-sm opeluce-select"
              @change="changeItemsPerPage"
            >
              <option value="10">10 por página</option>
              <option value="25">25 por página</option>
              <option value="50">50 por página</option>
              <option value="100">100 por página</option>
            </select>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';

const users = ref([]);
const loading = ref(false);
const error = ref('');

// Filter reactive object
const filters = ref({
  name: '',
  email: '',
  username: '',
  role: '',
  createdAt: ''
});

// Pagination reactive data
const currentPage = ref(1);
const itemsPerPage = ref(10);

// Computed property for filtered users (before pagination)
const filteredUsers = computed(() => {
  let filtered = users.value;

  // Filter by name
  if (filters.value.name) {
    filtered = filtered.filter(user => 
      user.name.toLowerCase().includes(filters.value.name.toLowerCase())
    );
  }

  // Filter by email
  if (filters.value.email) {
    filtered = filtered.filter(user => 
      user.email.toLowerCase().includes(filters.value.email.toLowerCase())
    );
  }

  // Filter by username
  if (filters.value.username) {
    filtered = filtered.filter(user => 
      user.username.toLowerCase().includes(filters.value.username.toLowerCase())
    );
  }

  // Filter by role
  if (filters.value.role) {
    if (filters.value.role === 'admin') {
      filtered = filtered.filter(user => user.role === true);
    } else if (filters.value.role === 'user') {
      filtered = filtered.filter(user => user.role === false);
    }
  }

  // Filter by creation date
  if (filters.value.createdAt) {
    filtered = filtered.filter(user => {
      const userDate = new Date(user.created_at).toISOString().split('T')[0];
      return userDate === filters.value.createdAt;
    });
  }

  return filtered;
});

// Computed properties for pagination
const totalItems = computed(() => filteredUsers.value.length);
const totalPages = computed(() => Math.ceil(totalItems.value / itemsPerPage.value));

const startItem = computed(() => {
  if (totalItems.value === 0) return 0;
  return (currentPage.value - 1) * itemsPerPage.value + 1;
});

const endItem = computed(() => {
  const end = currentPage.value * itemsPerPage.value;
  return end > totalItems.value ? totalItems.value : end;
});

// Computed property for paginated users (final result)
const paginatedUsers = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value;
  const end = start + itemsPerPage.value;
  return filteredUsers.value.slice(start, end);
});

// Computed property for visible page numbers
const visiblePages = computed(() => {
  const pages = [];
  const total = totalPages.value;
  const current = currentPage.value;
  
  if (total <= 7) {
    // Show all pages if 7 or fewer
    for (let i = 1; i <= total; i++) {
      pages.push(i);
    }
  } else {
    // Show smart pagination
    if (current <= 4) {
      // Show first 5 pages
      for (let i = 1; i <= 5; i++) {
        pages.push(i);
      }
      pages.push('...');
      pages.push(total);
    } else if (current >= total - 3) {
      // Show last 5 pages
      pages.push(1);
      pages.push('...');
      for (let i = total - 4; i <= total; i++) {
        pages.push(i);
      }
    } else {
      // Show pages around current
      pages.push(1);
      pages.push('...');
      for (let i = current - 1; i <= current + 1; i++) {
        pages.push(i);
      }
      pages.push('...');
      pages.push(total);
    }
  }
  
  return pages.filter(page => page !== '...' || !pages.includes('...'));
});

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
        // Redirect to login
        localStorage.removeItem('user');
        localStorage.removeItem('auth_token');
        window.location.href = '/login';
      } else if (err.response.status === 403) {
        error.value = 'Acceso denegado. No tiene permisos para ver los usuarios.';
        // Redirect to home page
        setTimeout(() => {
          window.location.href = '/';
        }, 2000);
      }
    }
  } finally {
    loading.value = false;
  }
}

function applyFilters() {
  // Reset to first page when filters change
  currentPage.value = 1;
  console.log('Filters applied:', filters.value);
}

function clearFilters() {
  filters.value = {
    name: '',
    email: '',
    username: '',
    role: '',
    createdAt: ''
  };
  currentPage.value = 1;
}

function changePage(page) {
  if (page >= 1 && page <= totalPages.value) {
    currentPage.value = page;
  }
}

function changeItemsPerPage() {
  currentPage.value = 1; // Reset to first page when changing items per page
}

async function deleteUser(id) {
  if (confirm('¿Está seguro de que desea eliminar este usuario? Esta acción no se puede deshacer.')) {
    try {
      await axios.delete(`/api/users/${id}`);
      await loadUsers(); // Reload the list
      
      // Adjust current page if necessary
      if (paginatedUsers.value.length === 0 && currentPage.value > 1) {
        currentPage.value--;
      }
      
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

.opeluce-action-btn {
  font-family: 'ProximaNovaSemibold', sans-serif;
  font-weight: 500;
  font-size: 14px;
  padding: 0.5rem;
  border-radius: 6px;
  border: 1.5px solid;
  transition: all 0.2s ease;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 36px;
  height: 36px;
  white-space: nowrap;
}

.opeluce-action-btn.btn-outline-primary {
  color: #009fe3;
  border-color: #009fe3;
}

.opeluce-action-btn.btn-outline-primary:hover {
  background-color: #009fe3;
  border-color: #009fe3;
  color: white;
  transform: translateY(-1px);
  box-shadow: 0 2px 4px rgba(0, 159, 227, 0.3);
}

.opeluce-action-btn.btn-outline-danger {
  color: #dc3545;
  border-color: #dc3545;
}

.opeluce-action-btn.btn-outline-danger:hover {
  background-color: #dc3545;
  border-color: #dc3545;
  color: white;
  transform: translateY(-1px);
  box-shadow: 0 2px 4px rgba(220, 53, 69, 0.3);
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

.opeluce-filters {
  background: #f8f9fa;
  border-bottom: 1px solid #eaeaea;
}

.opeluce-filter-label {
  color: #626161;
  font-weight: 600;
  font-family: 'ProximaNovaSemibold', sans-serif;
  font-size: 12px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin-bottom: 0.5rem;
}

.opeluce-filter-input {
  border: 1px solid #ddd;
  border-radius: 6px;
  padding: 0.5rem 0.75rem;
  font-family: 'ProximaNovaSemibold', sans-serif;
  font-size: 14px;
  transition: border-color 0.2s, box-shadow 0.2s;
}

.opeluce-filter-input:focus {
  border-color: #009fe3;
  box-shadow: 0 0 0 0.15rem rgba(0, 159, 227, 0.15);
  outline: none;
}

.opeluce-filter-input::placeholder {
  color: #999;
  font-size: 13px;
}

.pagination-controls {
  background-color: #f8f9fa;
  border-top: 1px solid #eaeaea;
  padding: 1rem 1.2rem;
  border-radius: 0 0 12px 12px;
  margin-bottom: 2rem;
}

.pagination-info {
  color: #626161;
  font-size: 0.875rem;
  font-family: 'ProximaNovaSemibold', sans-serif;
  font-weight: 500;
}

.items-per-page {
  min-width: 80px;
  border: 1px solid #ddd;
  border-radius: 6px;
  padding: 0.4rem 0.6rem;
  font-family: 'ProximaNovaSemibold', sans-serif;
  font-size: 14px;
}

.items-per-page:focus {
  border-color: #009fe3;
  box-shadow: 0 0 0 0.15rem rgba(0, 159, 227, 0.15);
  outline: none;
}

.pagination .page-link {
  color: #626161;
  border: 1px solid #ddd;
  padding: 0.5rem 0.75rem;
  font-family: 'ProximaNovaSemibold', sans-serif;
  font-weight: 500;
  border-radius: 6px;
  margin: 0 2px;
  transition: all 0.2s;
}

.pagination .page-link:hover {
  color: #009fe3;
  background-color: #f8fffe;
  border-color: #009fe3;
  transform: translateY(-1px);
}

.pagination .page-item.active .page-link {
  background-color: #009fe3;
  border-color: #009fe3;
  color: white;
  box-shadow: 0 2px 4px rgba(0, 159, 227, 0.3);
}

.pagination .page-item.disabled .page-link {
  color: #999;
  background-color: #f8f9fa;
  border-color: #ddd;
  transform: none;
}

.pagination .page-link:focus {
  box-shadow: 0 0 0 0.15rem rgba(0, 159, 227, 0.25);
}

@media (max-width: 768px) {
  .pagination-controls {
    flex-direction: column;
    gap: 1rem;
    text-align: center;
  }
  
  .pagination-controls .d-flex:first-child {
    justify-content: center;
  }
  
  .pagination {
    justify-content: center;
  }
  
  .pagination-info {
    text-align: center;
  }
  
  .pagination .page-link {
    padding: 0.4rem 0.6rem;
    font-size: 14px;
  }
}
</style>
