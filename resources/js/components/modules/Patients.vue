<template>
  <div class="patients">
    <div class="module-header">
      <h2>Gestión de Pacientes</h2>
      <button @click="showNewPatientForm = true" class="btn btn-primary">
        Nuevo Paciente
      </button>
    </div>

    <!-- Search and Filters -->
    <div class="search-filters">
      <div class="search-form">
        <input 
          v-model="searchQuery"
          type="text"
          placeholder="Buscar por DNI, nombre, apellido..."
          @keyup.enter="searchPatients"
        />
        <button @click="searchPatients" class="btn btn-secondary">Buscar</button>
      </div>
      
      <div class="filters">
        <select v-model="filterSex" @change="searchPatients">
          <option value="">Todos los sexos</option>
          <option value="M">Masculino</option>
          <option value="F">Femenino</option>
        </select>
        
        <select v-model="filterAge" @change="searchPatients">
          <option value="">Todas las edades</option>
          <option value="0-18">0-18 años</option>
          <option value="19-35">19-35 años</option>
          <option value="36-55">36-55 años</option>
          <option value="56+">56+ años</option>
        </select>
      </div>
    </div>

    <!-- Patients Table -->
    <div class="patients-table">
      <table>
        <thead>
          <tr>
            <th>DNI</th>
            <th>Nombres</th>
            <th>Apellidos</th>
            <th>Fecha Nac.</th>
            <th>Edad</th>
            <th>Sexo</th>
            <th>Teléfono</th>
            <th>Última Consulta</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="patient in patients" :key="patient.id">
            <td>{{ patient.dni }}</td>
            <td>{{ patient.nombres }}</td>
            <td>{{ patient.apellido_paterno }} {{ patient.apellido_materno }}</td>
            <td>{{ formatDate(patient.fecha_nacimiento) }}</td>
            <td>{{ calculateAge(patient.fecha_nacimiento) }}</td>
            <td>{{ patient.sexo }}</td>
            <td>{{ patient.telefono || 'N/A' }}</td>
            <td>{{ patient.ultima_consulta ? formatDate(patient.ultima_consulta) : 'Sin consultas' }}</td>
            <td class="actions">
              <button @click="viewPatient(patient)" class="btn-small btn-info">Ver</button>
              <button @click="editPatient(patient)" class="btn-small btn-warning">Editar</button>
              <button @click="viewHistory(patient)" class="btn-small btn-success">Historia</button>
              <button @click="deletePatient(patient)" class="btn-small btn-danger">Eliminar</button>
            </td>
          </tr>
        </tbody>
      </table>
      
      <div v-if="patients.length === 0" class="no-results">
        No se encontraron pacientes con los criterios de búsqueda.
      </div>
    </div>

    <!-- Pagination -->
    <div v-if="totalPages > 1" class="pagination">
      <button 
        @click="changePage(currentPage - 1)" 
        :disabled="currentPage === 1"
        class="btn btn-secondary"
      >
        Anterior
      </button>
      
      <span class="page-info">
        Página {{ currentPage }} de {{ totalPages }}
      </span>
      
      <button 
        @click="changePage(currentPage + 1)" 
        :disabled="currentPage === totalPages"
        class="btn btn-secondary"
      >
        Siguiente
      </button>
    </div>

    <!-- Patient Form Modal -->
    <div v-if="showNewPatientForm || showEditPatientForm" class="modal-overlay" @click="closeModal">
      <div class="modal-content" @click.stop>
        <div class="modal-header">
          <h3>{{ editingPatient ? 'Editar Paciente' : 'Nuevo Paciente' }}</h3>
          <button @click="closeModal" class="close-btn">&times;</button>
        </div>
        
        <form @submit.prevent="savePatient" class="patient-form">
          <div class="form-row">
            <div class="form-group">
              <label>DNI: *</label>
              <input 
                v-model="patientForm.dni" 
                type="text" 
                required 
                maxlength="8"
                pattern="[0-9]{8}"
                title="Ingrese 8 dígitos"
              />
            </div>
            <div class="form-group">
              <label>Nombres: *</label>
              <input v-model="patientForm.nombres" type="text" required />
            </div>
          </div>
          
          <div class="form-row">
            <div class="form-group">
              <label>Apellido Paterno: *</label>
              <input v-model="patientForm.apellido_paterno" type="text" required />
            </div>
            <div class="form-group">
              <label>Apellido Materno:</label>
              <input v-model="patientForm.apellido_materno" type="text" />
            </div>
          </div>
          
          <div class="form-row">
            <div class="form-group">
              <label>Fecha de Nacimiento: *</label>
              <input v-model="patientForm.fecha_nacimiento" type="date" required />
            </div>
            <div class="form-group">
              <label>Sexo: *</label>
              <select v-model="patientForm.sexo" required>
                <option value="">Seleccionar</option>
                <option value="M">Masculino</option>
                <option value="F">Femenino</option>
              </select>
            </div>
          </div>
          
          <div class="form-row">
            <div class="form-group">
              <label>Teléfono:</label>
              <input v-model="patientForm.telefono" type="tel" />
            </div>
            <div class="form-group">
              <label>Email:</label>
              <input v-model="patientForm.email" type="email" />
            </div>
          </div>
          
          <div class="form-group">
            <label>Dirección:</label>
            <textarea v-model="patientForm.direccion" rows="3"></textarea>
          </div>
          
          <div class="form-group">
            <label>Observaciones:</label>
            <textarea v-model="patientForm.observaciones" rows="3" placeholder="Información adicional del paciente..."></textarea>
          </div>
          
          <div class="form-actions">
            <button type="button" @click="closeModal" class="btn btn-secondary">Cancelar</button>
            <button type="submit" class="btn btn-primary">
              {{ editingPatient ? 'Actualizar' : 'Guardar' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Patient Details Modal -->
    <div v-if="showPatientDetails" class="modal-overlay" @click="closeDetailsModal">
      <div class="modal-content" @click.stop>
        <div class="modal-header">
          <h3>Detalles del Paciente</h3>
          <button @click="closeDetailsModal" class="close-btn">&times;</button>
        </div>
        
        <div v-if="selectedPatientDetails" class="patient-details">
          <div class="detail-section">
            <h4>Información Personal</h4>
            <div class="detail-grid">
              <div><strong>DNI:</strong> {{ selectedPatientDetails.dni }}</div>
              <div><strong>Nombres:</strong> {{ selectedPatientDetails.nombres }}</div>
              <div><strong>Apellidos:</strong> {{ selectedPatientDetails.apellido_paterno }} {{ selectedPatientDetails.apellido_materno }}</div>
              <div><strong>Fecha de Nacimiento:</strong> {{ formatDate(selectedPatientDetails.fecha_nacimiento) }}</div>
              <div><strong>Edad:</strong> {{ calculateAge(selectedPatientDetails.fecha_nacimiento) }} años</div>
              <div><strong>Sexo:</strong> {{ selectedPatientDetails.sexo === 'M' ? 'Masculino' : 'Femenino' }}</div>
              <div><strong>Teléfono:</strong> {{ selectedPatientDetails.telefono || 'No registrado' }}</div>
              <div><strong>Email:</strong> {{ selectedPatientDetails.email || 'No registrado' }}</div>
            </div>
          </div>
          
          <div v-if="selectedPatientDetails.direccion" class="detail-section">
            <h4>Dirección</h4>
            <p>{{ selectedPatientDetails.direccion }}</p>
          </div>
          
          <div v-if="selectedPatientDetails.observaciones" class="detail-section">
            <h4>Observaciones</h4>
            <p>{{ selectedPatientDetails.observaciones }}</p>
          </div>
          
          <div class="detail-section">
            <h4>Historial de Consultas</h4>
            <div v-if="selectedPatientDetails.consultas && selectedPatientDetails.consultas.length > 0">
              <div v-for="consulta in selectedPatientDetails.consultas" :key="consulta.id" class="consulta-item">
                <strong>{{ formatDate(consulta.fecha) }}</strong> - {{ consulta.tipo }}
              </div>
            </div>
            <div v-else>
              <p>No hay consultas registradas.</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'Patients',
  data() {
    return {
      patients: [],
      searchQuery: '',
      filterSex: '',
      filterAge: '',
      currentPage: 1,
      totalPages: 1,
      showNewPatientForm: false,
      showEditPatientForm: false,
      showPatientDetails: false,
      editingPatient: null,
      selectedPatientDetails: null,
      patientForm: {
        dni: '',
        nombres: '',
        apellido_paterno: '',
        apellido_materno: '',
        fecha_nacimiento: '',
        sexo: '',
        telefono: '',
        email: '',
        direccion: '',
        observaciones: ''
      }
    };
  },
  methods: {
    async searchPatients() {
      try {
        const params = new URLSearchParams({
          q: this.searchQuery,
          sex: this.filterSex,
          age: this.filterAge,
          page: this.currentPage
        });
        
        const response = await fetch(`/api/patients?${params}`);
        const data = await response.json();
        
        this.patients = data.data;
        this.totalPages = data.last_page;
      } catch (error) {
        console.error('Error searching patients:', error);
      }
    },
    
    async savePatient() {
      try {
        const url = this.editingPatient 
          ? `/api/patients/${this.editingPatient.id}` 
          : '/api/patients';
        
        const method = this.editingPatient ? 'PUT' : 'POST';
        
        const response = await fetch(url, {
          method,
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          },
          body: JSON.stringify(this.patientForm)
        });
        
        if (response.ok) {
          this.closeModal();
          this.searchPatients();
          alert('Paciente guardado exitosamente');
        } else {
          const errorData = await response.json();
          alert('Error: ' + (errorData.message || 'No se pudo guardar el paciente'));
        }
      } catch (error) {
        console.error('Error saving patient:', error);
        alert('Error de conexión');
      }
    },
    
    editPatient(patient) {
      this.editingPatient = patient;
      this.patientForm = { ...patient };
      this.showEditPatientForm = true;
    },
    
    async viewPatient(patient) {
      try {
        const response = await fetch(`/api/patients/${patient.id}`);
        const data = await response.json();
        this.selectedPatientDetails = data;
        this.showPatientDetails = true;
      } catch (error) {
        console.error('Error loading patient details:', error);
      }
    },
    
    viewHistory(patient) {
      // Navigate to patient history view
      console.log('View history for:', patient);
      // You can emit an event to parent component or use router navigation
    },
    
    async deletePatient(patient) {
      if (confirm(`¿Está seguro de eliminar al paciente ${patient.nombres} ${patient.apellido_paterno}?`)) {
        try {
          const response = await fetch(`/api/patients/${patient.id}`, {
            method: 'DELETE',
            headers: {
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
          });
          
          if (response.ok) {
            this.searchPatients();
            alert('Paciente eliminado exitosamente');
          }
        } catch (error) {
          console.error('Error deleting patient:', error);
          alert('Error al eliminar paciente');
        }
      }
    },
    
    changePage(page) {
      this.currentPage = page;
      this.searchPatients();
    },
    
    closeModal() {
      this.showNewPatientForm = false;
      this.showEditPatientForm = false;
      this.editingPatient = null;
      this.resetForm();
    },
    
    closeDetailsModal() {
      this.showPatientDetails = false;
      this.selectedPatientDetails = null;
    },
    
    resetForm() {
      this.patientForm = {
        dni: '',
        nombres: '',
        apellido_paterno: '',
        apellido_materno: '',
        fecha_nacimiento: '',
        sexo: '',
        telefono: '',
        email: '',
        direccion: '',
        observaciones: ''
      };
    },
    
    formatDate(date) {
      if (!date) return 'N/A';
      return new Date(date).toLocaleDateString('es-ES');
    },
    
    calculateAge(birthDate) {
      if (!birthDate) return 'N/A';
      const today = new Date();
      const birth = new Date(birthDate);
      let age = today.getFullYear() - birth.getFullYear();
      const monthDiff = today.getMonth() - birth.getMonth();
      
      if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birth.getDate())) {
        age--;
      }
      
      return age;
    }
  },
  
  mounted() {
    this.searchPatients();
  }
};
</script>

<style scoped>
.patients {
  max-width: 1400px;
}

.module-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}

.module-header h2 {
  margin: 0;
  color: #333;
}

.search-filters {
  background: white;
  padding: 1.5rem;
  border-radius: 8px;
  margin-bottom: 2rem;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.search-form {
  display: flex;
  gap: 1rem;
  margin-bottom: 1rem;
}

.search-form input {
  flex: 1;
  padding: 0.75rem;
  border: 1px solid #ddd;
  border-radius: 4px;
}

.filters {
  display: flex;
  gap: 1rem;
}

.filters select {
  padding: 0.5rem;
  border: 1px solid #ddd;
  border-radius: 4px;
}

.patients-table {
  background: white;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  margin-bottom: 2rem;
}

table {
  width: 100%;
  border-collapse: collapse;
}

th, td {
  padding: 1rem;
  text-align: left;
  border-bottom: 1px solid #eee;
}

th {
  background: #f8f9fa;
  font-weight: bold;
  color: #333;
}

.actions {
  white-space: nowrap;
}

.no-results {
  padding: 2rem;
  text-align: center;
  color: #666;
}

.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 1rem;
  margin-bottom: 2rem;
}

.page-info {
  color: #666;
}

.btn {
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-weight: 500;
  transition: background-color 0.3s;
}

.btn-primary {
  background: #007bff;
  color: white;
}

.btn-secondary {
  background: #6c757d;
  color: white;
}

.btn-small {
  padding: 0.25rem 0.5rem;
  font-size: 0.8rem;
  margin-right: 0.25rem;
}

.btn-info {
  background: #17a2b8;
  color: white;
}

.btn-warning {
  background: #ffc107;
  color: #212529;
}

.btn-success {
  background: #28a745;
  color: white;
}

.btn-danger {
  background: #dc3545;
  color: white;
}

.btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0,0,0,0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal-content {
  background: white;
  border-radius: 8px;
  width: 90%;
  max-width: 800px;
  max-height: 80vh;
  overflow-y: auto;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 1.5rem;
  border-bottom: 1px solid #eee;
}

.close-btn {
  background: none;
  border: none;
  font-size: 1.5rem;
  cursor: pointer;
  color: #999;
}

.patient-form {
  padding: 1.5rem;
}

.form-row {
  display: flex;
  gap: 1rem;
  margin-bottom: 1rem;
}

.form-group {
  flex: 1;
}

.form-group label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: bold;
  color: #333;
}

.form-group input,
.form-group select,
.form-group textarea {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #ddd;
  border-radius: 4px;
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  margin-top: 2rem;
}

.patient-details {
  padding: 1.5rem;
}

.detail-section {
  margin-bottom: 2rem;
}

.detail-section h4 {
  margin-bottom: 1rem;
  color: #333;
  border-bottom: 1px solid #eee;
  padding-bottom: 0.5rem;
}

.detail-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1rem;
}

.detail-grid div {
  padding: 0.5rem 0;
}

.consulta-item {
  padding: 0.5rem;
  margin-bottom: 0.5rem;
  background: #f8f9fa;
  border-radius: 4px;
}
</style>
