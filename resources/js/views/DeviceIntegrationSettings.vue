<template>
  <div class="container py-4">
    <h2 class="mb-4"><i class="fas fa-cogs me-2"></i>Configuración Dispositivos</h2>
    <div class="row g-4">
      <!-- Refractometer -->
      <div class="col-lg-6">
        <div class="card h-100">
          <div class="card-header bg-primary text-white">Auto Kerato-Refractómetro</div>
            <div class="card-body">
              <form @submit.prevent="saveRefractometer">
                <div class="mb-2">
                  <label class="form-label">Nombre</label>
                  <input v-model="refractometer.name" class="form-control" required />
                </div>
                <div class="mb-2">
                  <label class="form-label">Ruta UNC</label>
                  <input v-model="refractometer.unc_path" class="form-control" placeholder="\\\\127.0.0.1\\kr" />
                </div>
                <div class="mb-2">
                  <label class="form-label">Ruta Montaje Local</label>
                  <input v-model="refractometer.local_mount_path" class="form-control" placeholder="/mnt/kr" />
                </div>
                <div class="mb-2">
                  <label class="form-label">Ruta Alterna</label>
                  <input v-model="refractometer.alternate_path" class="form-control" />
                </div>
                <div class="row g-2">
                  <div class="col-6">
                    <label class="form-label">Intervalo (s)</label>
                    <input type="number" min="5" v-model.number="refractometer.poll_interval_seconds" class="form-control" />
                  </div>
                  <div class="col-6">
                    <label class="form-label">Historial Máx.</label>
                    <input type="number" min="10" v-model.number="refractometer.history_limit" class="form-control" />
                  </div>
                </div>
                <div class="mb-2 mt-2">
                  <label class="form-label">Patrón Archivos</label>
                  <input v-model="refractometer.file_pattern" class="form-control" />
                </div>
                <div class="form-check form-switch mb-3">
                  <input class="form-check-input" type="checkbox" v-model="refractometer.enabled" id="refEnabled" />
                  <label class="form-check-label" for="refEnabled">Habilitado</label>
                </div>
                <button class="btn btn-primary" :disabled="savingRef">{{ savingRef ? 'Guardando...' : 'Guardar' }}</button>
              </form>
            </div>
        </div>
      </div>
      <!-- Lensometer -->
      <div class="col-lg-6">
        <div class="card h-100">
          <div class="card-header bg-secondary text-white">Lensómetro (PostgreSQL)</div>
            <div class="card-body">
              <form @submit.prevent="saveLensometer">
                <div class="mb-2">
                  <label class="form-label">Nombre</label>
                  <input v-model="lensometer.name" class="form-control" required />
                </div>
                <div class="row g-2">
                  <div class="col-8">
                    <label class="form-label">Host</label>
                    <input v-model="lensometer.host" class="form-control" />
                  </div>
                  <div class="col-4">
                    <label class="form-label">Puerto</label>
                    <input type="number" min="1" max="65535" v-model.number="lensometer.port" class="form-control" />
                  </div>
                </div>
                <div class="row g-2 mt-1">
                  <div class="col-6">
                    <label class="form-label">BD</label>
                    <input v-model="lensometer.database" class="form-control" />
                  </div>
                  <div class="col-6">
                    <label class="form-label">Usuario</label>
                    <input v-model="lensometer.username" class="form-control" />
                  </div>
                </div>
                <div class="mb-2 mt-2">
                  <label class="form-label">Password (cifrado futuro)</label>
                  <input v-model="lensometer.password_encrypted" type="password" class="form-control" />
                </div>
                <div class="row g-2">
                  <div class="col-6">
                    <label class="form-label">Esquema</label>
                    <input v-model="lensometer.schema" class="form-control" />
                  </div>
                  <div class="col-6">
                    <label class="form-label">Tabla</label>
                    <input v-model="lensometer.table_name" class="form-control" />
                  </div>
                </div>
                <div class="form-check form-switch mt-3 mb-3">
                  <input class="form-check-input" type="checkbox" v-model="lensometer.enabled" id="lensEnabled" />
                  <label class="form-check-label" for="lensEnabled">Habilitado</label>
                </div>
                <button class="btn btn-secondary" :disabled="savingLens">{{ savingLens ? 'Guardando...' : 'Guardar' }}</button>
              </form>
            </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const refractometer = ref({ name: 'default', poll_interval_seconds: 30, history_limit: 500, file_pattern: '*.xml', enabled: true });
const lensometer = ref({ name: 'default', port: 5432, table_name: 'lensmeterresulttbl', enabled: true });
const savingRef = ref(false);
const savingLens = ref(false);

async function loadSettings() {
  try {
    const { data } = await axios.get('/api/device-integration-settings');
    if (data.refractometer?.length) refractometer.value = { ...data.refractometer[0] };
    if (data.lensometer?.length) lensometer.value = { ...data.lensometer[0] };
  } catch (e) {
    console.error('Error loading settings', e);
  }
}

async function saveRefractometer() {
  savingRef.value = true;
  try {
    const { data } = await axios.post('/api/device-integration-settings/refractometer', refractometer.value);
    refractometer.value = data;
    alert('Configuración de Refractómetro guardada');
  } catch (e) {
    alert('Error guardando refractómetro');
  } finally { savingRef.value = false; }
}

async function saveLensometer() {
  savingLens.value = true;
  try {
    const { data } = await axios.post('/api/device-integration-settings/lensometer', lensometer.value);
    lensometer.value = data;
    alert('Configuración de Lensómetro guardada');
  } catch (e) {
    alert('Error guardando lensómetro');
  } finally { savingLens.value = false; }
}

onMounted(loadSettings);
</script>

<style scoped>
h2 { font-weight:600; }
label { font-weight:600; }
</style>
