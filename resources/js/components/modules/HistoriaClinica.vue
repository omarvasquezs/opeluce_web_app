<template>
  <div class="historia-clinica">
    <div class="module-header">
      <h2>Historia Clínica</h2>
      <button @click="showNewPatientForm = true" class="btn btn-primary">
        Nuevo Paciente
      </button>
    </div>

    <!-- Search Section -->
    <div class="search-section">
      <div class="search-form">
        <input 
          v-model="searchQuery"
          type="text"
          placeholder="Buscar por DNI, nombre o apellido..."
          @keyup.enter="searchPatients"
        />
        <button @click="searchPatients" class="btn btn-secondary">Buscar</button>
      </div>
    </div>

    <!-- New Patient Form Modal -->
    <div v-if="showNewPatientForm" class="modal-overlay" @click="closeModal">
      <div class="modal-content" @click.stop>
        <div class="modal-header">
          <h3>{{ editingPatient ? 'Editar Paciente' : 'Nuevo Paciente' }}</h3>
          <button @click="closeModal" class="close-btn">&times;</button>
        </div>
        
        <form @submit.prevent="savePatient" class="patient-form">
          <div class="form-row">
            <div class="form-group">
              <label>DNI:</label>
              <input v-model="patientForm.dni" type="text" required maxlength="8" />
            </div>
            <div class="form-group">
              <label>Nombres:</label>
              <input v-model="patientForm.nombres" type="text" required />
            </div>
          </div>
          
          <div class="form-row">
            <div class="form-group">
              <label>Apellido Paterno:</label>
              <input v-model="patientForm.apellido_paterno" type="text" required />
            </div>
            <div class="form-group">
              <label>Apellido Materno:</label>
              <input v-model="patientForm.apellido_materno" type="text" />
            </div>
          </div>
          
          <div class="form-row">
            <div class="form-group">
              <label>Fecha de Nacimiento:</label>
              <input v-model="patientForm.fecha_nacimiento" type="date" required />
            </div>
            <div class="form-group">
              <label>Sexo:</label>
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
          
          <div class="form-actions">
            <button type="button" @click="closeModal" class="btn btn-secondary">Cancelar</button>
            <button type="submit" class="btn btn-primary">
              {{ editingPatient ? 'Actualizar' : 'Guardar' }}
            </button>
          </div>
        </form>
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
            <th>Sexo</th>
            <th>Teléfono</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="patient in patients" :key="patient.id">
            <td>{{ patient.dni }}</td>
            <td>{{ patient.nombres }}</td>
            <td>{{ patient.apellido_paterno }} {{ patient.apellido_materno }}</td>
            <td>{{ formatDate(patient.fecha_nacimiento) }}</td>
            <td>{{ patient.sexo }}</td>
            <td>{{ patient.telefono }}</td>
            <td class="actions">
              <button @click="editPatient(patient)" class="btn-small btn-warning">Editar</button>
              <button @click="viewHistory(patient)" class="btn-small btn-info">Historia</button>
              <button @click="deletePatient(patient)" class="btn-small btn-danger">Eliminar</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script>
export default {
  name: 'HistoriaClinica',
  data() {
    return {
      patients: [],
      searchQuery: '',
      showNewPatientForm: false,
      editingPatient: null,
      patientForm: {
        dni: '',
        nombres: '',
        apellido_paterno: '',
        apellido_materno: '',
        fecha_nacimiento: '',
        sexo: '',
        telefono: '',
        email: '',
        direccion: ''
      }
    };
  },
  methods: {
    async searchPatients() {
      try {
        const response = await fetch(`/api/patients/search?q=${this.searchQuery}`);
        const data = await response.json();
        this.patients = data;
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
        }
      } catch (error) {
        console.error('Error saving patient:', error);
      }
    },
    
    editPatient(patient) {
      this.editingPatient = patient;
      this.patientForm = { ...patient };
      this.showNewPatientForm = true;
    },
    
    async deletePatient(patient) {
      if (confirm('¿Está seguro de eliminar este paciente?')) {
        try {
          await fetch(`/api/patients/${patient.id}`, {
            method: 'DELETE',
            headers: {
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
          });
          this.searchPatients();
        } catch (error) {
          console.error('Error deleting patient:', error);
        }
      }
    },
    
    viewHistory(patient) {
      // Redirect to patient history view
      console.log('View history for:', patient);
    },
    
    closeModal() {
      this.showNewPatientForm = false;
      this.editingPatient = null;
      this.resetForm();
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
        direccion: ''
      };
    },
    
    formatDate(date) {
      return new Date(date).toLocaleDateString('es-ES');
    }
  },
  
  mounted() {
    this.searchPatients();
  }
};
</script>

<style scoped>
.historia-clinica {
  max-width: 1200px;
}

.module-header {
  display: flex;
  justify-content: between;
  align-items: center;
  margin-bottom: 2rem;
}

.module-header h2 {
  margin: 0;
  color: #333;
}

.search-section {
  background: white;
  padding: 1.5rem;
  border-radius: 8px;
  margin-bottom: 2rem;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.search-form {
  display: flex;
  gap: 1rem;
}

.search-form input {
  flex: 1;
  padding: 0.75rem;
  border: 1px solid #ddd;
  border-radius: 4px;
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

.btn-warning {
  background: #ffc107;
  color: #212529;
}

.btn-info {
  background: #17a2b8;
  color: white;
}

.btn-danger {
  background: #dc3545;
  color: white;
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
  max-width: 600px;
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

.patients-table {
  background: white;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
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
</style>
