<template>
  <div class="auto-keratometry">
    <div class="module-header">
      <h2>Auto Keratometría</h2>
      <button @click="importXML" class="btn btn-primary">Importar XML</button>
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
    </div>

    <!-- Keratometry Results -->
    <div v-if="selectedPatient" class="keratometry-form">
      <form @submit.prevent="saveKeratometry">
        <div class="form-section">
          <h3>Resultados de Keratometría - Ojo Derecho</h3>
          <div class="form-row">
            <div class="form-group">
              <label>K1 (D):</label>
              <input v-model="keratometryForm.od_k1" type="number" step="0.01" />
            </div>
            <div class="form-group">
              <label>K2 (D):</label>
              <input v-model="keratometryForm.od_k2" type="number" step="0.01" />
            </div>
            <div class="form-group">
              <label>Eje K1 (°):</label>
              <input v-model="keratometryForm.od_eje_k1" type="number" min="0" max="180" />
            </div>
            <div class="form-group">
              <label>Eje K2 (°):</label>
              <input v-model="keratometryForm.od_eje_k2" type="number" min="0" max="180" />
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Astigmatismo (D):</label>
              <input v-model="keratometryForm.od_astigmatismo" type="number" step="0.01" readonly />
            </div>
            <div class="form-group">
              <label>Curvatura Media (D):</label>
              <input v-model="keratometryForm.od_curvatura_media" type="number" step="0.01" readonly />
            </div>
          </div>
        </div>

        <div class="form-section">
          <h3>Resultados de Keratometría - Ojo Izquierdo</h3>
          <div class="form-row">
            <div class="form-group">
              <label>K1 (D):</label>
              <input v-model="keratometryForm.oi_k1" type="number" step="0.01" />
            </div>
            <div class="form-group">
              <label>K2 (D):</label>
              <input v-model="keratometryForm.oi_k2" type="number" step="0.01" />
            </div>
            <div class="form-group">
              <label>Eje K1 (°):</label>
              <input v-model="keratometryForm.oi_eje_k1" type="number" min="0" max="180" />
            </div>
            <div class="form-group">
              <label>Eje K2 (°):</label>
              <input v-model="keratometryForm.oi_eje_k2" type="number" min="0" max="180" />
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Astigmatismo (D):</label>
              <input v-model="keratometryForm.oi_astigmatismo" type="number" step="0.01" readonly />
            </div>
            <div class="form-group">
              <label>Curvatura Media (D):</label>
              <input v-model="keratometryForm.oi_curvatura_media" type="number" step="0.01" readonly />
            </div>
          </div>
        </div>

        <div class="form-section">
          <h3>Observaciones</h3>
          <div class="form-group">
            <textarea v-model="keratometryForm.observaciones" rows="4" placeholder="Ingrese observaciones adicionales..."></textarea>
          </div>
        </div>

        <div class="form-actions">
          <button type="button" @click="calculateValues" class="btn btn-secondary">Calcular</button>
          <button type="submit" class="btn btn-primary">Guardar Keratometría</button>
        </div>
      </form>
    </div>

    <!-- Recent Keratometry Results -->
    <div v-if="selectedPatient && recentKeratometry.length > 0" class="recent-results">
      <h3>Resultados Anteriores</h3>
      <div class="results-table">
        <table>
          <thead>
            <tr>
              <th>Fecha</th>
              <th>OD K1/K2</th>
              <th>OD Astig.</th>
              <th>OI K1/K2</th>
              <th>OI Astig.</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="result in recentKeratometry" :key="result.id">
              <td>{{ formatDate(result.created_at) }}</td>
              <td>{{ result.od_k1 }}/{{ result.od_k2 }}</td>
              <td>{{ result.od_astigmatismo }}</td>
              <td>{{ result.oi_k1 }}/{{ result.oi_k2 }}</td>
              <td>{{ result.oi_astigmatismo }}</td>
              <td>
                <button @click="viewResult(result)" class="btn-small btn-info">Ver</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- XML Import Modal -->
    <div v-if="showXMLImport" class="modal-overlay" @click="closeXMLModal">
      <div class="modal-content" @click.stop>
        <div class="modal-header">
          <h3>Importar Archivo XML</h3>
          <button @click="closeXMLModal" class="close-btn">&times;</button>
        </div>
        
        <div class="xml-import-content">
          <div class="form-group">
            <label>Seleccionar archivo XML:</label>
            <input type="file" @change="handleFileSelect" accept=".xml" />
          </div>
          
          <div v-if="xmlPreview" class="xml-preview">
            <h4>Vista previa:</h4>
            <pre>{{ xmlPreview }}</pre>
          </div>
          
          <div class="form-actions">
            <button @click="closeXMLModal" class="btn btn-secondary">Cancelar</button>
            <button @click="processXML" :disabled="!selectedFile" class="btn btn-primary">Procesar</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'AutoKeratometry',
  data() {
    return {
      patientSearch: '',
      searchResults: [],
      selectedPatient: null,
      recentKeratometry: [],
      showXMLImport: false,
      selectedFile: null,
      xmlPreview: '',
      keratometryForm: {
        od_k1: '',
        od_k2: '',
        od_eje_k1: '',
        od_eje_k2: '',
        od_astigmatismo: '',
        od_curvatura_media: '',
        oi_k1: '',
        oi_k2: '',
        oi_eje_k1: '',
        oi_eje_k2: '',
        oi_astigmatismo: '',
        oi_curvatura_media: '',
        observaciones: ''
      }
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
      this.loadRecentKeratometry();
    },
    
    async loadRecentKeratometry() {
      try {
        const response = await fetch(`/api/patients/${this.selectedPatient.id}/keratometry`);
        const data = await response.json();
        this.recentKeratometry = data;
      } catch (error) {
        console.error('Error loading recent keratometry:', error);
      }
    },
    
    calculateValues() {
      // Calculate astigmatism for OD
      if (this.keratometryForm.od_k1 && this.keratometryForm.od_k2) {
        this.keratometryForm.od_astigmatismo = Math.abs(
          parseFloat(this.keratometryForm.od_k1) - parseFloat(this.keratometryForm.od_k2)
        ).toFixed(2);
        
        this.keratometryForm.od_curvatura_media = (
          (parseFloat(this.keratometryForm.od_k1) + parseFloat(this.keratometryForm.od_k2)) / 2
        ).toFixed(2);
      }
      
      // Calculate astigmatism for OI
      if (this.keratometryForm.oi_k1 && this.keratometryForm.oi_k2) {
        this.keratometryForm.oi_astigmatismo = Math.abs(
          parseFloat(this.keratometryForm.oi_k1) - parseFloat(this.keratometryForm.oi_k2)
        ).toFixed(2);
        
        this.keratometryForm.oi_curvatura_media = (
          (parseFloat(this.keratometryForm.oi_k1) + parseFloat(this.keratometryForm.oi_k2)) / 2
        ).toFixed(2);
      }
    },
    
    async saveKeratometry() {
      this.calculateValues();
      
      try {
        const response = await fetch('/api/keratometry', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          },
          body: JSON.stringify({
            patient_id: this.selectedPatient.id,
            ...this.keratometryForm
          })
        });
        
        if (response.ok) {
          alert('Keratometría guardada exitosamente');
          this.loadRecentKeratometry();
        }
      } catch (error) {
        console.error('Error saving keratometry:', error);
      }
    },
    
    importXML() {
      this.showXMLImport = true;
    },
    
    closeXMLModal() {
      this.showXMLImport = false;
      this.selectedFile = null;
      this.xmlPreview = '';
    },
    
    handleFileSelect(event) {
      this.selectedFile = event.target.files[0];
      if (this.selectedFile) {
        const reader = new FileReader();
        reader.onload = (e) => {
          this.xmlPreview = e.target.result.substring(0, 500) + '...';
        };
        reader.readAsText(this.selectedFile);
      }
    },
    
    async processXML() {
      if (!this.selectedFile) return;
      
      const formData = new FormData();
      formData.append('xml_file', this.selectedFile);
      
      try {
        const response = await fetch('/api/keratometry/import-xml', {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          },
          body: formData
        });
        
        const data = await response.json();
        
        if (response.ok) {
          // Fill form with imported data
          Object.assign(this.keratometryForm, data);
          this.closeXMLModal();
          alert('XML importado exitosamente');
        }
      } catch (error) {
        console.error('Error processing XML:', error);
      }
    },
    
    viewResult(result) {
      Object.assign(this.keratometryForm, result);
    },
    
    formatDate(date) {
      return new Date(date).toLocaleDateString('es-ES');
    }
  }
};
</script>

<style scoped>
.auto-keratometry {
  max-width: 1200px;
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

.keratometry-form {
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

.form-group input[readonly] {
  background-color: #f8f9fa;
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

.btn-small {
  padding: 0.25rem 0.5rem;
  font-size: 0.8rem;
}

.btn-info {
  background: #17a2b8;
  color: white;
}

.recent-results {
  background: white;
  padding: 1.5rem;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  margin-bottom: 2rem;
}

.results-table table {
  width: 100%;
  border-collapse: collapse;
}

.results-table th,
.results-table td {
  padding: 0.75rem;
  text-align: left;
  border-bottom: 1px solid #eee;
}

.results-table th {
  background: #f8f9fa;
  font-weight: bold;
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

.xml-import-content {
  padding: 1.5rem;
}

.xml-preview {
  margin: 1rem 0;
  padding: 1rem;
  background: #f8f9fa;
  border-radius: 4px;
  max-height: 200px;
  overflow-y: auto;
}

.xml-preview pre {
  margin: 0;
  font-size: 0.8rem;
}
</style>
