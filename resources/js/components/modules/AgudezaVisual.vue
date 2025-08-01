<template>
  <div class="agudeza-visual">
    <div class="module-header">
      <h2>Examen de Agudeza Visual y Refracción</h2>
    </div>

    <!-- Patient Selection -->
    <div class="patient-selection">
      <div class="form-group">
        <label>Seleccionar Paciente:</label>
        <div class="patient-search">
          <input 
            v-model="patientSearch"
            type="text"
            placeholder="Buscar paciente por DNI o nombre..."
            @input="searchPatients"
          />
          <div v-if="searchResults.length > 0" class="search-dropdown">
            <div 
              v-for="patient in searchResults" 
              :key="patient.id"
              @click="selectPatient(patient)"
              class="search-result"
            >
              {{ patient.dni }} - {{ patient.nombres }} {{ patient.apellido_paterno }}
            </div>
          </div>
        </div>
      </div>
      
      <div v-if="selectedPatient" class="selected-patient">
        <strong>Paciente:</strong> {{ selectedPatient.nombres }} {{ selectedPatient.apellido_paterno }}
        <strong>DNI:</strong> {{ selectedPatient.dni }}
      </div>
    </div>

    <!-- Agudeza Visual Form -->
    <div v-if="selectedPatient" class="exam-form">
      <form @submit.prevent="saveExam">
        <div class="form-section">
          <h3>Agudeza Visual Sin Corrección</h3>
          <div class="form-row">
            <div class="form-group">
              <label>Ojo Derecho (OD):</label>
              <select v-model="examForm.av_od_sin_correccion">
                <option value="">Seleccionar</option>
                <option v-for="value in agudezaValues" :key="value" :value="value">{{ value }}</option>
              </select>
            </div>
            <div class="form-group">
              <label>Ojo Izquierdo (OI):</label>
              <select v-model="examForm.av_oi_sin_correccion">
                <option value="">Seleccionar</option>
                <option v-for="value in agudezaValues" :key="value" :value="value">{{ value }}</option>
              </select>
            </div>
            <div class="form-group">
              <label>Ambos Ojos (AO):</label>
              <select v-model="examForm.av_ao_sin_correccion">
                <option value="">Seleccionar</option>
                <option v-for="value in agudezaValues" :key="value" :value="value">{{ value }}</option>
              </select>
            </div>
          </div>
        </div>

        <div class="form-section">
          <h3>Refracción Ojo Derecho</h3>
          <div class="form-row">
            <div class="form-group">
              <label>Esfera:</label>
              <input v-model="examForm.od_esfera" type="number" step="0.25" />
            </div>
            <div class="form-group">
              <label>Cilindro:</label>
              <input v-model="examForm.od_cilindro" type="number" step="0.25" />
            </div>
            <div class="form-group">
              <label>Eje:</label>
              <input v-model="examForm.od_eje" type="number" min="0" max="180" />
            </div>
            <div class="form-group">
              <label>AV con Corrección:</label>
              <select v-model="examForm.od_av_corregida">
                <option value="">Seleccionar</option>
                <option v-for="value in agudezaValues" :key="value" :value="value">{{ value }}</option>
              </select>
            </div>
          </div>
        </div>

        <div class="form-section">
          <h3>Refracción Ojo Izquierdo</h3>
          <div class="form-row">
            <div class="form-group">
              <label>Esfera:</label>
              <input v-model="examForm.oi_esfera" type="number" step="0.25" />
            </div>
            <div class="form-group">
              <label>Cilindro:</label>
              <input v-model="examForm.oi_cilindro" type="number" step="0.25" />
            </div>
            <div class="form-group">
              <label>Eje:</label>
              <input v-model="examForm.oi_eje" type="number" min="0" max="180" />
            </div>
            <div class="form-group">
              <label>AV con Corrección:</label>
              <select v-model="examForm.oi_av_corregida">
                <option value="">Seleccionar</option>
                <option v-for="value in agudezaValues" :key="value" :value="value">{{ value }}</option>
              </select>
            </div>
          </div>
        </div>

        <div class="form-section">
          <h3>Observaciones</h3>
          <div class="form-group">
            <textarea v-model="examForm.observaciones" rows="4" placeholder="Ingrese observaciones adicionales..."></textarea>
          </div>
        </div>

        <div class="form-actions">
          <button type="button" @click="clearForm" class="btn btn-secondary">Limpiar</button>
          <button type="submit" class="btn btn-primary">Guardar Examen</button>
        </div>
      </form>
    </div>

    <!-- Recent Exams -->
    <div v-if="selectedPatient" class="recent-exams">
      <h3>Exámenes Anteriores</h3>
      <div class="exams-list">
        <div v-for="exam in recentExams" :key="exam.id" class="exam-card">
          <div class="exam-date">{{ formatDate(exam.created_at) }}</div>
          <div class="exam-details">
            <div><strong>OD:</strong> {{ exam.od_esfera }} {{ exam.od_cilindro }} x {{ exam.od_eje }}</div>
            <div><strong>OI:</strong> {{ exam.oi_esfera }} {{ exam.oi_cilindro }} x {{ exam.oi_eje }}</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'AgudezaVisual',
  data() {
    return {
      patientSearch: '',
      searchResults: [],
      selectedPatient: null,
      recentExams: [],
      examForm: {
        av_od_sin_correccion: '',
        av_oi_sin_correccion: '',
        av_ao_sin_correccion: '',
        od_esfera: '',
        od_cilindro: '',
        od_eje: '',
        od_av_corregida: '',
        oi_esfera: '',
        oi_cilindro: '',
        oi_eje: '',
        oi_av_corregida: '',
        observaciones: ''
      },
      agudezaValues: [
        '20/20', '20/25', '20/30', '20/40', '20/50', '20/60', '20/70', '20/80', 
        '20/100', '20/120', '20/140', '20/160', '20/200', 'CD', 'MM', 'PL'
      ]
    };
  },
  methods: {
    async searchPatients() {
      if (this.patientSearch.length < 2) {
        this.searchResults = [];
        return;
      }
      
      try {
        const response = await fetch(`/api/patients/search?q=${this.patientSearch}`);
        const data = await response.json();
        this.searchResults = data;
      } catch (error) {
        console.error('Error searching patients:', error);
      }
    },
    
    selectPatient(patient) {
      this.selectedPatient = patient;
      this.patientSearch = `${patient.dni} - ${patient.nombres} ${patient.apellido_paterno}`;
      this.searchResults = [];
      this.loadRecentExams();
    },
    
    async loadRecentExams() {
      try {
        const response = await fetch(`/api/patients/${this.selectedPatient.id}/agudeza-visual`);
        const data = await response.json();
        this.recentExams = data;
      } catch (error) {
        console.error('Error loading recent exams:', error);
      }
    },
    
    async saveExam() {
      try {
        const response = await fetch('/api/agudeza-visual', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          },
          body: JSON.stringify({
            patient_id: this.selectedPatient.id,
            ...this.examForm
          })
        });
        
        if (response.ok) {
          alert('Examen guardado exitosamente');
          this.clearForm();
          this.loadRecentExams();
        }
      } catch (error) {
        console.error('Error saving exam:', error);
      }
    },
    
    clearForm() {
      this.examForm = {
        av_od_sin_correccion: '',
        av_oi_sin_correccion: '',
        av_ao_sin_correccion: '',
        od_esfera: '',
        od_cilindro: '',
        od_eje: '',
        od_av_corregida: '',
        oi_esfera: '',
        oi_cilindro: '',
        oi_eje: '',
        oi_av_corregida: '',
        observaciones: ''
      };
    },
    
    formatDate(date) {
      return new Date(date).toLocaleDateString('es-ES');
    }
  }
};
</script>

<style scoped>
.agudeza-visual {
  max-width: 1200px;
}

.module-header {
  margin-bottom: 2rem;
}

.module-header h2 {
  margin: 0;
  color: #333;
}

.patient-selection {
  background: white;
  padding: 1.5rem;
  border-radius: 8px;
  margin-bottom: 2rem;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.patient-search {
  position: relative;
}

.patient-search input {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #ddd;
  border-radius: 4px;
}

.search-dropdown {
  position: absolute;
  top: 100%;
  left: 0;
  right: 0;
  background: white;
  border: 1px solid #ddd;
  border-top: none;
  border-radius: 0 0 4px 4px;
  max-height: 200px;
  overflow-y: auto;
  z-index: 100;
}

.search-result {
  padding: 0.75rem;
  cursor: pointer;
  border-bottom: 1px solid #eee;
}

.search-result:hover {
  background: #f8f9fa;
}

.selected-patient {
  margin-top: 1rem;
  padding: 1rem;
  background: #e7f3ff;
  border-radius: 4px;
  display: flex;
  gap: 2rem;
}

.exam-form {
  background: white;
  padding: 2rem;
  border-radius: 8px;
  margin-bottom: 2rem;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.form-section {
  margin-bottom: 2rem;
  padding-bottom: 2rem;
  border-bottom: 1px solid #eee;
}

.form-section:last-child {
  border-bottom: none;
  margin-bottom: 0;
  padding-bottom: 0;
}

.form-section h3 {
  margin-bottom: 1rem;
  color: #333;
  font-size: 1.2rem;
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

.recent-exams {
  background: white;
  padding: 1.5rem;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.recent-exams h3 {
  margin-bottom: 1rem;
  color: #333;
}

.exams-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.exam-card {
  padding: 1rem;
  border: 1px solid #eee;
  border-radius: 4px;
  background: #f8f9fa;
}

.exam-date {
  font-weight: bold;
  margin-bottom: 0.5rem;
  color: #666;
}

.exam-details {
  display: flex;
  gap: 2rem;
}
</style>
