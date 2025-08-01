<template>
  <div class="reports">
    <div class="module-header">
      <h2>Reportes y Estadísticas</h2>
    </div>

    <!-- Report Filters -->
    <div class="filters-section">
      <div class="filter-group">
        <label>Tipo de Reporte:</label>
        <select v-model="selectedReport" @change="generateReport">
          <option value="">Seleccionar reporte</option>
          <option value="patients_summary">Resumen de Pacientes</option>
          <option value="exams_by_date">Exámenes por Fecha</option>
          <option value="age_distribution">Distribución por Edad</option>
          <option value="prescription_analysis">Análisis de Prescripciones</option>
          <option value="monthly_activity">Actividad Mensual</option>
        </select>
      </div>
      
      <div class="filter-group">
        <label>Fecha Desde:</label>
        <input v-model="dateFrom" type="date" @change="generateReport" />
      </div>
      
      <div class="filter-group">
        <label>Fecha Hasta:</label>
        <input v-model="dateTo" type="date" @change="generateReport" />
      </div>
      
      <div class="filter-actions">
        <button @click="generateReport" class="btn btn-primary">Generar</button>
        <button @click="exportReport" :disabled="!reportData" class="btn btn-success">Exportar PDF</button>
      </div>
    </div>

    <!-- Statistics Cards -->
    <div v-if="stats" class="stats-grid">
      <div class="stat-card">
        <div class="stat-value">{{ stats.total_patients }}</div>
        <div class="stat-label">Total Pacientes</div>
      </div>
      
      <div class="stat-card">
        <div class="stat-value">{{ stats.total_exams }}</div>
        <div class="stat-label">Exámenes Realizados</div>
      </div>
      
      <div class="stat-card">
        <div class="stat-value">{{ stats.exams_this_month }}</div>
        <div class="stat-label">Exámenes Este Mes</div>
      </div>
      
      <div class="stat-card">
        <div class="stat-value">{{ stats.avg_age }}</div>
        <div class="stat-label">Edad Promedio</div>
      </div>
    </div>

    <!-- Report Content -->
    <div v-if="reportData" class="report-content">
      <!-- Patients Summary Report -->
      <div v-if="selectedReport === 'patients_summary'" class="report-section">
        <h3>Resumen de Pacientes</h3>
        <div class="summary-table">
          <table>
            <thead>
              <tr>
                <th>DNI</th>
                <th>Paciente</th>
                <th>Edad</th>
                <th>Sexo</th>
                <th>Último Examen</th>
                <th>Total Exámenes</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="patient in reportData" :key="patient.id">
                <td>{{ patient.dni }}</td>
                <td>{{ patient.nombres }} {{ patient.apellido_paterno }}</td>
                <td>{{ calculateAge(patient.fecha_nacimiento) }}</td>
                <td>{{ patient.sexo }}</td>
                <td>{{ patient.ultimo_examen ? formatDate(patient.ultimo_examen) : 'Sin exámenes' }}</td>
                <td>{{ patient.total_examenes }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Exams by Date Report -->
      <div v-if="selectedReport === 'exams_by_date'" class="report-section">
        <h3>Exámenes por Fecha</h3>
        <div class="exams-table">
          <table>
            <thead>
              <tr>
                <th>Fecha</th>
                <th>Paciente</th>
                <th>Tipo de Examen</th>
                <th>Resultado OD</th>
                <th>Resultado OI</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="exam in reportData" :key="exam.id">
                <td>{{ formatDate(exam.fecha) }}</td>
                <td>{{ exam.paciente }}</td>
                <td>{{ exam.tipo_examen }}</td>
                <td>{{ exam.resultado_od }}</td>
                <td>{{ exam.resultado_oi }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Age Distribution Chart -->
      <div v-if="selectedReport === 'age_distribution'" class="report-section">
        <h3>Distribución por Edad</h3>
        <div class="chart-container">
          <canvas ref="ageChart" width="400" height="200"></canvas>
        </div>
        <div class="age-distribution-table">
          <table>
            <thead>
              <tr>
                <th>Rango de Edad</th>
                <th>Cantidad</th>
                <th>Porcentaje</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="range in reportData" :key="range.range">
                <td>{{ range.range }}</td>
                <td>{{ range.count }}</td>
                <td>{{ range.percentage }}%</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Prescription Analysis -->
      <div v-if="selectedReport === 'prescription_analysis'" class="report-section">
        <h3>Análisis de Prescripciones</h3>
        <div class="prescription-stats">
          <div class="prescription-grid">
            <div class="prescription-card">
              <h4>Miopía</h4>
              <div class="prescription-value">{{ reportData.miopia }}%</div>
            </div>
            <div class="prescription-card">
              <h4>Hipermetropía</h4>
              <div class="prescription-value">{{ reportData.hipermetropia }}%</div>
            </div>
            <div class="prescription-card">
              <h4>Astigmatismo</h4>
              <div class="prescription-value">{{ reportData.astigmatismo }}%</div>
            </div>
            <div class="prescription-card">
              <h4>Presbicia</h4>
              <div class="prescription-value">{{ reportData.presbicia }}%</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Monthly Activity -->
      <div v-if="selectedReport === 'monthly_activity'" class="report-section">
        <h3>Actividad Mensual</h3>
        <div class="chart-container">
          <canvas ref="monthlyChart" width="400" height="200"></canvas>
        </div>
        <div class="monthly-table">
          <table>
            <thead>
              <tr>
                <th>Mes</th>
                <th>Nuevos Pacientes</th>
                <th>Exámenes</th>
                <th>Promedio Diario</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="month in reportData" :key="month.month">
                <td>{{ month.month }}</td>
                <td>{{ month.nuevos_pacientes }}</td>
                <td>{{ month.examenes }}</td>
                <td>{{ month.promedio_diario }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- No Report Selected -->
    <div v-else class="no-report">
      <div class="no-report-content">
        <h3>Seleccione un tipo de reporte</h3>
        <p>Elija el tipo de reporte que desea generar y configure las fechas si es necesario.</p>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'Reports',
  data() {
    return {
      selectedReport: '',
      dateFrom: '',
      dateTo: '',
      reportData: null,
      stats: null,
      loading: false
    };
  },
  methods: {
    async loadStats() {
      try {
        const response = await fetch('/api/reports/stats');
        const data = await response.json();
        this.stats = data;
      } catch (error) {
        console.error('Error loading stats:', error);
      }
    },
    
    async generateReport() {
      if (!this.selectedReport) return;
      
      this.loading = true;
      this.reportData = null;
      
      try {
        const params = new URLSearchParams({
          type: this.selectedReport,
          date_from: this.dateFrom,
          date_to: this.dateTo
        });
        
        const response = await fetch(`/api/reports/generate?${params}`);
        const data = await response.json();
        
        this.reportData = data;
        
        // Generate charts if needed
        this.$nextTick(() => {
          if (this.selectedReport === 'age_distribution') {
            this.drawAgeChart();
          } else if (this.selectedReport === 'monthly_activity') {
            this.drawMonthlyChart();
          }
        });
        
      } catch (error) {
        console.error('Error generating report:', error);
        alert('Error al generar el reporte');
      } finally {
        this.loading = false;
      }
    },
    
    async exportReport() {
      if (!this.reportData) return;
      
      try {
        const params = new URLSearchParams({
          type: this.selectedReport,
          date_from: this.dateFrom,
          date_to: this.dateTo,
          format: 'pdf'
        });
        
        const response = await fetch(`/api/reports/export?${params}`);
        
        if (response.ok) {
          const blob = await response.blob();
          const url = window.URL.createObjectURL(blob);
          const a = document.createElement('a');
          a.href = url;
          a.download = `reporte_${this.selectedReport}_${new Date().toISOString().split('T')[0]}.pdf`;
          document.body.appendChild(a);
          a.click();
          document.body.removeChild(a);
          window.URL.revokeObjectURL(url);
        }
      } catch (error) {
        console.error('Error exporting report:', error);
        alert('Error al exportar el reporte');
      }
    },
    
    drawAgeChart() {
      if (!this.$refs.ageChart || !this.reportData) return;
      
      const ctx = this.$refs.ageChart.getContext('2d');
      const data = this.reportData;
      
      // Simple bar chart implementation
      ctx.clearRect(0, 0, ctx.canvas.width, ctx.canvas.height);
      
      const barWidth = ctx.canvas.width / data.length - 10;
      const maxValue = Math.max(...data.map(d => d.count));
      
      data.forEach((item, index) => {
        const barHeight = (item.count / maxValue) * (ctx.canvas.height - 40);
        const x = index * (barWidth + 10) + 5;
        const y = ctx.canvas.height - barHeight - 20;
        
        ctx.fillStyle = '#007bff';
        ctx.fillRect(x, y, barWidth, barHeight);
        
        ctx.fillStyle = '#333';
        ctx.font = '12px Arial';
        ctx.fillText(item.range, x, ctx.canvas.height - 5);
        ctx.fillText(item.count, x + barWidth/2 - 10, y - 5);
      });
    },
    
    drawMonthlyChart() {
      if (!this.$refs.monthlyChart || !this.reportData) return;
      
      const ctx = this.$refs.monthlyChart.getContext('2d');
      const data = this.reportData;
      
      // Simple line chart implementation
      ctx.clearRect(0, 0, ctx.canvas.width, ctx.canvas.height);
      
      const pointSpacing = ctx.canvas.width / (data.length - 1);
      const maxValue = Math.max(...data.map(d => d.examenes));
      
      ctx.strokeStyle = '#007bff';
      ctx.lineWidth = 2;
      ctx.beginPath();
      
      data.forEach((item, index) => {
        const x = index * pointSpacing;
        const y = ctx.canvas.height - (item.examenes / maxValue) * (ctx.canvas.height - 40) - 20;
        
        if (index === 0) {
          ctx.moveTo(x, y);
        } else {
          ctx.lineTo(x, y);
        }
        
        // Draw point
        ctx.fillStyle = '#007bff';
        ctx.beginPath();
        ctx.arc(x, y, 4, 0, 2 * Math.PI);
        ctx.fill();
        
        // Draw label
        ctx.fillStyle = '#333';
        ctx.font = '10px Arial';
        ctx.fillText(item.month.substring(0, 3), x - 15, ctx.canvas.height - 5);
      });
      
      ctx.stroke();
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
    this.loadStats();
    
    // Set default date range (last 30 days)
    const today = new Date();
    const thirtyDaysAgo = new Date(today.getTime() - (30 * 24 * 60 * 60 * 1000));
    
    this.dateTo = today.toISOString().split('T')[0];
    this.dateFrom = thirtyDaysAgo.toISOString().split('T')[0];
  }
};
</script>

<style scoped>
.reports {
  max-width: 1400px;
}

.module-header {
  margin-bottom: 2rem;
}

.module-header h2 {
  margin: 0;
  color: #333;
}

.filters-section {
  background: white;
  padding: 1.5rem;
  border-radius: 8px;
  margin-bottom: 2rem;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  display: flex;
  gap: 1rem;
  align-items: end;
  flex-wrap: wrap;
}

.filter-group {
  flex: 1;
  min-width: 200px;
}

.filter-group label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: bold;
  color: #333;
}

.filter-group select,
.filter-group input {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #ddd;
  border-radius: 4px;
}

.filter-actions {
  display: flex;
  gap: 1rem;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
  margin-bottom: 2rem;
}

.stat-card {
  background: white;
  padding: 1.5rem;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  text-align: center;
}

.stat-value {
  font-size: 2rem;
  font-weight: bold;
  color: #007bff;
  margin-bottom: 0.5rem;
}

.stat-label {
  color: #666;
  font-size: 0.9rem;
}

.report-content {
  background: white;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.report-section {
  padding: 2rem;
}

.report-section h3 {
  margin-bottom: 1.5rem;
  color: #333;
  border-bottom: 2px solid #007bff;
  padding-bottom: 0.5rem;
}

.summary-table table,
.exams-table table,
.age-distribution-table table,
.monthly-table table {
  width: 100%;
  border-collapse: collapse;
}

.summary-table th,
.summary-table td,
.exams-table th,
.exams-table td,
.age-distribution-table th,
.age-distribution-table td,
.monthly-table th,
.monthly-table td {
  padding: 0.75rem;
  text-align: left;
  border-bottom: 1px solid #eee;
}

.summary-table th,
.exams-table th,
.age-distribution-table th,
.monthly-table th {
  background: #f8f9fa;
  font-weight: bold;
}

.chart-container {
  margin: 2rem 0;
  text-align: center;
}

.chart-container canvas {
  border: 1px solid #ddd;
  border-radius: 4px;
}

.prescription-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
  margin: 2rem 0;
}

.prescription-card {
  background: #f8f9fa;
  padding: 1.5rem;
  border-radius: 8px;
  text-align: center;
  border-left: 4px solid #007bff;
}

.prescription-card h4 {
  margin-bottom: 1rem;
  color: #333;
}

.prescription-value {
  font-size: 2rem;
  font-weight: bold;
  color: #007bff;
}

.no-report {
  background: white;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  padding: 4rem;
  text-align: center;
}

.no-report-content h3 {
  color: #666;
  margin-bottom: 1rem;
}

.no-report-content p {
  color: #999;
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

.btn-success {
  background: #28a745;
  color: white;
}

.btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}
</style>
