<template>
  <div class="lensmeter">
    <div class="module-header">
      <h2>Lensómetro</h2>
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

    <!-- Lensmeter Measurements -->
    <div v-if="selectedPatient" class="lensmeter-form">
      <form @submit.prevent="saveLensmeter">
        <div class="form-section">
          <h3>Medición de Lentes - Ojo Derecho</h3>
          <div class="form-row">
            <div class="form-group">
              <label>Esfera (D):</label>
              <input v-model="lensmeterForm.od_esfera" type="number" step="0.25" />
            </div>
            <div class="form-group">
              <label>Cilindro (D):</label>
              <input v-model="lensmeterForm.od_cilindro" type="number" step="0.25" />
            </div>
            <div class="form-group">
              <label>Eje (°):</label>
              <input v-model="lensmeterForm.od_eje" type="number" min="0" max="180" />
            </div>
            <div class="form-group">
              <label>Adición (D):</label>
              <input v-model="lensmeterForm.od_adicion" type="number" step="0.25" />
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Prisma Base (△):</label>
              <input v-model="lensmeterForm.od_prisma_base" type="number" step="0.1" />
            </div>
            <div class="form-group">
              <label>Dirección Prisma:</label>
              <select v-model="lensmeterForm.od_prisma_direccion">
                <option value="">Seleccionar</option>
                <option value="BI">Base Interna</option>
                <option value="BE">Base Externa</option>
                <option value="BS">Base Superior</option>
                <option value="BF">Base Inferior</option>
              </select>
            </div>
          </div>
        </div>

        <div class="form-section">
          <h3>Medición de Lentes - Ojo Izquierdo</h3>
          <div class="form-row">
            <div class="form-group">
              <label>Esfera (D):</label>
              <input v-model="lensmeterForm.oi_esfera" type="number" step="0.25" />
            </div>
            <div class="form-group">
              <label>Cilindro (D):</label>
              <input v-model="lensmeterForm.oi_cilindro" type="number" step="0.25" />
            </div>
            <div class="form-group">
              <label>Eje (°):</label>
              <input v-model="lensmeterForm.oi_eje" type="number" min="0" max="180" />
            </div>
            <div class="form-group">
              <label>Adición (D):</label>
              <input v-model="lensmeterForm.oi_adicion" type="number" step="0.25" />
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Prisma Base (△):</label>
              <input v-model="lensmeterForm.oi_prisma_base" type="number" step="0.1" />
            </div>
            <div class="form-group">
              <label>Dirección Prisma:</label>
              <select v-model="lensmeterForm.oi_prisma_direccion">
                <option value="">Seleccionar</option>
                <option value="BI">Base Interna</option>
                <option value="BE">Base Externa</option>
                <option value="BS">Base Superior</option>
                <option value="BF">Base Inferior</option>
              </select>
            </div>
          </div>
        </div>

        <div class="form-section">
          <h3>Información del Lente</h3>
          <div class="form-row">
            <div class="form-group">
              <label>Tipo de Lente:</label>
              <select v-model="lensmeterForm.tipo_lente">
                <option value="">Seleccionar</option>
                <option value="monofocal">Monofocal</option>
                <option value="bifocal">Bifocal</option>
                <option value="progresivo">Progresivo</option>
                <option value="ocupacional">Ocupacional</option>
              </select>
            </div>
            <div class="form-group">
              <label>Material:</label>
              <select v-model="lensmeterForm.material">
                <option value="">Seleccionar</option>
                <option value="organico">Orgánico</option>
                <option value="policarbonato">Policarbonato</option>
                <option value="trivex">Trivex</option>
                <option value="cristal">Cristal</option>
              </select>
            </div>
            <div class="form-group">
              <label>Índice de Refracción:</label>
              <select v-model="lensmeterForm.indice_refraccion">
                <option value="">Seleccionar</option>
                <option value="1.5">1.5</option>
                <option value="1.56">1.56</option>
                <option value="1.59">1.59</option>
                <option value="1.61">1.61</option>
                <option value="1.67">1.67</option>
                <option value="1.74">1.74</option>
              </select>
            </div>
          </div>
          
          <div class="form-row">
            <div class="form-group">
              <label>Tratamiento:</label>
              <div class="checkbox-group">
                <label><input type="checkbox" v-model="lensmeterForm.antirreflex"> Antirreflex</label>
                <label><input type="checkbox" v-model="lensmeterForm.fotocromico"> Fotocromático</label>
                <label><input type="checkbox" v-model="lensmeterForm.polarizado"> Polarizado</label>
                <label><input type="checkbox" v-model="lensmeterForm.uv"> Protección UV</label>
              </div>
            </div>
          </div>
        </div>

        <div class="form-section">
          <h3>Observaciones</h3>
          <div class="form-group">
            <textarea v-model="lensmeterForm.observaciones" rows="4" placeholder="Ingrese observaciones sobre la medición..."></textarea>
          </div>
        </div>

        <div class="form-actions">
          <button type="button" @click="clearForm" class="btn btn-secondary">Limpiar</button>
          <button type="submit" class="btn btn-primary">Guardar Medición</button>
        </div>
      </form>
    </div>

    <!-- Recent Measurements -->
    <div v-if="selectedPatient && recentMeasurements.length > 0" class="recent-measurements">
      <h3>Mediciones Anteriores</h3>
      <div class="measurements-table">
        <table>
          <thead>
            <tr>
              <th>Fecha</th>
              <th>OD</th>
              <th>OI</th>
              <th>Tipo</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="measurement in recentMeasurements" :key="measurement.id">
              <td>{{ formatDate(measurement.created_at) }}</td>
              <td>{{ formatPrescription(measurement, 'od') }}</td>
              <td>{{ formatPrescription(measurement, 'oi') }}</td>
              <td>{{ measurement.tipo_lente }}</td>
              <td>
                <button @click="viewMeasurement(measurement)" class="btn-small btn-info">Ver</button>
                <button @click="editMeasurement(measurement)" class="btn-small btn-warning">Editar</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'Lensmeter',
  data() {
    return {
      patientSearch: '',
      searchResults: [],
      selectedPatient: null,
      recentMeasurements: [],
      lensmeterForm: {
        od_esfera: '',
        od_cilindro: '',
        od_eje: '',
        od_adicion: '',
        od_prisma_base: '',
        od_prisma_direccion: '',
        oi_esfera: '',
        oi_cilindro: '',
        oi_eje: '',
        oi_adicion: '',
        oi_prisma_base: '',
        oi_prisma_direccion: '',
        tipo_lente: '',
        material: '',
        indice_refraccion: '',
        antirreflex: false,
        fotocromico: false,
        polarizado: false,
        uv: false,
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
      this.loadRecentMeasurements();
    },
    
    async loadRecentMeasurements() {
      try {
        const response = await fetch(`/api/patients/${this.selectedPatient.id}/lensmeter`);
        const data = await response.json();
        this.recentMeasurements = data;
      } catch (error) {
        console.error('Error loading recent measurements:', error);
      }
    },
    
    async saveLensmeter() {
      try {
        const response = await fetch('/api/lensmeter', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          },
          body: JSON.stringify({
            patient_id: this.selectedPatient.id,
            ...this.lensmeterForm
          })
        });
        
        if (response.ok) {
          alert('Medición guardada exitosamente');
          this.clearForm();
          this.loadRecentMeasurements();
        }
      } catch (error) {
        console.error('Error saving measurement:', error);
      }
    },
    
    clearForm() {
      this.lensmeterForm = {
        od_esfera: '',
        od_cilindro: '',
        od_eje: '',
        od_adicion: '',
        od_prisma_base: '',
        od_prisma_direccion: '',
        oi_esfera: '',
        oi_cilindro: '',
        oi_eje: '',
        oi_adicion: '',
        oi_prisma_base: '',
        oi_prisma_direccion: '',
        tipo_lente: '',
        material: '',
        indice_refraccion: '',
        antirreflex: false,
        fotocromico: false,
        polarizado: false,
        uv: false,
        observaciones: ''
      };
    },
    
    viewMeasurement(measurement) {
      // Show measurement details in a modal or expand view
      console.log('View measurement:', measurement);
    },
    
    editMeasurement(measurement) {
      Object.assign(this.lensmeterForm, measurement);
    },
    
    formatPrescription(measurement, eye) {
      const esfera = measurement[`${eye}_esfera`];
      const cilindro = measurement[`${eye}_cilindro`];
      const eje = measurement[`${eye}_eje`];
      
      let prescription = esfera ? `${esfera}` : '';
      if (cilindro) {
        prescription += ` ${cilindro} x ${eje}°`;
      }
      
      return prescription || 'N/A';
    },
    
    formatDate(date) {
      return new Date(date).toLocaleDateString('es-ES');
    }
  }
};
</script>

<style scoped>
.lensmeter {
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

.lensmeter-form {
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

.checkbox-group {
  display: flex;
  flex-wrap: wrap;
  gap: 1rem;
}

.checkbox-group label {
  display: flex;
  align-items: center;
  font-weight: normal;
  margin-bottom: 0;
}

.checkbox-group input[type="checkbox"] {
  width: auto;
  margin-right: 0.5rem;
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

.recent-measurements {
  background: white;
  padding: 1.5rem;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.recent-measurements h3 {
  margin-bottom: 1rem;
  color: #333;
}

.measurements-table table {
  width: 100%;
  border-collapse: collapse;
}

.measurements-table th,
.measurements-table td {
  padding: 0.75rem;
  text-align: left;
  border-bottom: 1px solid #eee;
}

.measurements-table th {
  background: #f8f9fa;
  font-weight: bold;
}
</style>
