<template>
  <div class="registros-optometricos">
    <!-- Progress Steps -->
    <div class="steps-header">
      <div class="container-fluid">
        <div class="row">
          <div class="col-12">
            <div class="progress-steps">
              <div class="step" :class="{ active: currentStep === 1, completed: currentStep > 1 }">
                <div class="step-circle">
                  <i class="fas fa-user-edit"></i>
                </div>
                <span class="step-label">Historia Clínica</span>
              </div>
              <div class="step-line" :class="{ active: currentStep > 1 }"></div>
              <div class="step" :class="{ active: currentStep === 2, completed: currentStep > 2 }">
                <div class="step-circle">
                  <i class="fas fa-eye"></i>
                </div>
                <span class="step-label">Agudeza Visual / Refracción</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Step 1: Historia Clínica -->
    <div v-if="currentStep === 1" class="step-content">
      <div class="container-fluid">
        <div class="row">
          <div class="col-12">
            <div class="form-card">
              <div class="form-header">
                <h2 class="form-title">
                  <i class="fas fa-user-edit me-2"></i>
                  Historia Clínica
                </h2>
                <div class="hc-number">
                  <label>HC N°</label>
                  <input 
                    type="text" 
                    v-model="historiaClinica.hcNumber" 
                    class="form-control hc-input"
                  />
                </div>
              </div>

              <div class="form-body">
                <div class="row g-3">
                  <!-- Patient Search -->
                  <div class="col-md-8">
                    <label class="form-label">Paciente:</label>
                    <div class="input-group">
                      <input 
                        type="text" 
                        v-model="historiaClinica.paciente" 
                        class="form-control"
                        placeholder="Buscar paciente..."
                      />
                      <button 
                        @click="buscarPaciente" 
                        class="btn btn-primary"
                        type="button"
                      >
                        BUSCAR
                      </button>
                    </div>
                  </div>

                  <div class="col-md-4">
                    <label class="form-label">DNI o DOI:</label>
                    <input 
                      type="text" 
                      v-model="historiaClinica.dni" 
                      class="form-control"
                    />
                  </div>

                  <!-- Birth Date, Age, Attention Date -->
                  <div class="col-md-4">
                    <label class="form-label">Fecha de Nacimiento:</label>
                    <input 
                      type="date" 
                      v-model="historiaClinica.fechaNacimiento" 
                      class="form-control date-input"
                    />
                  </div>

                  <div class="col-md-4">
                    <label class="form-label">Edad:</label>
                    <input 
                      type="number" 
                      v-model="historiaClinica.edad" 
                      class="form-control"
                      min="0" 
                      max="120"
                      readonly
                    />
                  </div>

                  <div class="col-md-4">
                    <label class="form-label">Fecha de Atención:</label>
                    <input 
                      type="date" 
                      v-model="historiaClinica.fechaAtencion" 
                      class="form-control date-input"
                    />
                  </div>

                  <!-- Race, Sex, Attention Time -->
                  <div class="col-md-4">
                    <label class="form-label">Raza:</label>
                    <input 
                      type="text" 
                      v-model="historiaClinica.raza" 
                      class="form-control"
                    />
                  </div>

                  <div class="col-md-4">
                    <label class="form-label">Sexo:</label>
                    <div class="form-check-container">
                      <div class="form-check">
                        <input 
                          class="form-check-input" 
                          type="radio" 
                          v-model="historiaClinica.sexo" 
                          value="F" 
                          id="sexoF"
                        >
                        <label class="form-check-label" for="sexoF">F</label>
                      </div>
                      <div class="form-check">
                        <input 
                          class="form-check-input" 
                          type="radio" 
                          v-model="historiaClinica.sexo" 
                          value="M" 
                          id="sexoM"
                        >
                        <label class="form-check-label" for="sexoM">M</label>
                      </div>
                    </div>
                  </div>

                  <div class="col-md-4">
                    <label class="form-label">Hora de Atención:</label>
                    <input 
                      type="time" 
                      v-model="historiaClinica.horaAtencion" 
                      class="form-control"
                    />
                  </div>

                  <!-- Occupation, Affiliation -->
                  <div class="col-md-6">
                    <label class="form-label">Ocupación:</label>
                    <input 
                      type="text" 
                      v-model="historiaClinica.ocupacion" 
                      class="form-control"
                    />
                  </div>

                  <div class="col-md-6">
                    <label class="form-label">Afiliación:</label>
                    <input 
                      type="text" 
                      v-model="historiaClinica.afiliacion" 
                      class="form-control"
                    />
                  </div>

                  <!-- Current Address -->
                  <div class="col-12">
                    <label class="form-label">Domicilio Actual:</label>
                    <input 
                      type="text" 
                      v-model="historiaClinica.domicilioActual" 
                      class="form-control"
                    />
                  </div>

                  <!-- Companion and DNI -->
                  <div class="col-md-6">
                    <label class="form-label">Acompañante:</label>
                    <input 
                      type="text" 
                      v-model="historiaClinica.acompanante" 
                      class="form-control"
                    />
                  </div>

                  <div class="col-md-6">
                    <label class="form-label">DNI Acompañante:</label>
                    <input 
                      type="text" 
                      v-model="historiaClinica.dniAcompanante" 
                      class="form-control"
                    />
                  </div>
                </div>

                <!-- Action Buttons -->
                <div class="form-actions">
                  <button class="btn btn-secondary btn-lg">
                    CERRAR
                  </button>
                  <button 
                    @click="revisarAgudezaVisual" 
                    class="btn btn-primary btn-lg ms-3"
                    :disabled="!canProceedToStep2"
                  >
                    <i class="fas fa-arrow-right me-2"></i>
                    SIGUIENTE
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Step 2: Agudeza Visual / Refracción -->
    <div v-if="currentStep === 2" class="step-content">
      <div class="container-fluid">
        <div class="row">
          <div class="col-12">
            <div class="form-card">
              <div class="form-header">
                <h2 class="form-title">
                  <i class="fas fa-eye me-2"></i>
                  Agudeza Visual / Refracción
                </h2>
                <div class="header-controls">
                  <div class="examination-date">
                    <label class="date-label">Fecha de Examen:</label>
                    <input 
                      type="date" 
                      v-model="examinationDate" 
                      class="form-control date-input examination-date-input"
                    />
                  </div>
                  <div class="category-selector">
                    <div class="select-wrapper">
                      <select v-model="selectedCategory" class="form-select">
                        <option value="">-- Seleccione una Categoría --</option>
                        <option value="refraccion">Refracción</option>
                        <option value="lensometria">Lensometría</option>
                      </select>
                      <i class="fas fa-chevron-down select-caret"></i>
                    </div>
                    <button 
                      @click="consultarRegistros" 
                      class="btn btn-info ms-2"
                      :disabled="!selectedCategory || isLoadingRecords"
                    >
                      <i v-if="isLoadingRecords" class="fas fa-spinner fa-spin me-2"></i>
                      Consultar Registros
                    </button>
                  </div>
                </div>
              </div>

              <div class="form-body">
                <!-- Two Column Layout -->
                <div class="row g-4">
                  <!-- Left Column - Vision Tests & Refraction -->
                  <div class="col-lg-6">
                    <!-- Agudeza Visual -->
                    <div class="measurement-card">
                      <div class="card-header">
                        <h5 class="card-title">
                          <i class="fas fa-eye me-2 text-primary"></i>
                          Agudeza Visual
                        </h5>
                      </div>
                      <div class="card-body">
                        <div class="measurement-table modern">
                          <div class="table-header">
                            <span class="eye-col"></span>
                            <span class="header-cell">AVsc</span>
                            <span class="header-cell">AVcc</span>
                            <span class="header-cell">AE</span>
                          </div>
                          <div class="table-row">
                            <span class="eye-label od">OD</span>
                            <input type="text" class="measurement-input" v-model="agudezaVisual.od.avsc" placeholder="0.00">
                            <input type="text" class="measurement-input" v-model="agudezaVisual.od.avcc" placeholder="0.00">
                            <input type="text" class="measurement-input" v-model="agudezaVisual.od.ae" placeholder="0.00">
                          </div>
                          <div class="table-row">
                            <span class="eye-label oi">OI</span>
                            <input type="text" class="measurement-input" v-model="agudezaVisual.oi.avsc" placeholder="0.00">
                            <input type="text" class="measurement-input" v-model="agudezaVisual.oi.avcc" placeholder="0.00">
                            <input type="text" class="measurement-input" v-model="agudezaVisual.oi.ae" placeholder="0.00">
                          </div>
                        </div>
                      </div>
                    </div>

                    <!-- Refracción Automática -->
                    <div class="measurement-card">
                      <div class="card-header">
                        <h5 class="card-title">
                          <i class="fas fa-calculator me-2 text-success"></i>
                          Refracción Automática
                        </h5>
                      </div>
                      <div class="card-body">
                        <div class="measurement-table modern">
                          <div class="table-header">
                            <span class="eye-col"></span>
                            <span class="header-cell">ESF</span>
                            <span class="header-cell">CIL</span>
                            <span class="header-cell">EJE</span>
                          </div>
                          <div class="table-row">
                            <span class="eye-label od">OD</span>
                            <input type="text" class="measurement-input" v-model="refraccionAutomatica.od.esf" placeholder="±0.00">
                            <input type="text" class="measurement-input" v-model="refraccionAutomatica.od.cil" placeholder="±0.00">
                            <input type="text" class="measurement-input" v-model="refraccionAutomatica.od.eje" placeholder="000°">
                          </div>
                          <div class="table-row">
                            <span class="eye-label oi">OI</span>
                            <input type="text" class="measurement-input" v-model="refraccionAutomatica.oi.esf" placeholder="±0.00">
                            <input type="text" class="measurement-input" v-model="refraccionAutomatica.oi.cil" placeholder="±0.00">
                            <input type="text" class="measurement-input" v-model="refraccionAutomatica.oi.eje" placeholder="000°">
                          </div>
                        </div>
                        <div class="additional-field">
                          <label class="field-label">DIP (mm)</label>
                          <input type="text" class="measurement-input single" v-model="refraccionAutomatica.dip" placeholder="65">
                        </div>
                      </div>
                    </div>

                    <!-- Queratometría -->
                    <div class="measurement-card">
                      <div class="card-header">
                        <h5 class="card-title">
                          <i class="fas fa-chart-line me-2 text-warning"></i>
                          Queratometría
                        </h5>
                      </div>
                      <div class="card-body">
                        <div class="keratometry-table">
                          <div class="kera-row">
                            <span class="eye-label od">OD</span>
                            <div class="kera-fields">
                              <input type="text" class="measurement-input" v-model="queratometria.od.valor1" placeholder="K1">
                              <span class="field-separator">CIL</span>
                              <input type="text" class="measurement-input" v-model="queratometria.od.cil1" placeholder="0.00">
                            </div>
                          </div>
                          <div class="kera-row">
                            <span class="eye-label oi">OI</span>
                            <div class="kera-fields">
                              <input type="text" class="measurement-input" v-model="queratometria.oi.valor1" placeholder="K1">
                              <span class="field-separator">CIL</span>
                              <input type="text" class="measurement-input" v-model="queratometria.oi.cil1" placeholder="0.00">
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>

                    <!-- Medida de Vista -->
                    <div class="measurement-card">
                      <div class="card-header">
                        <h5 class="card-title">
                          <i class="fas fa-glasses me-2 text-info"></i>
                          Medida de Vista
                        </h5>
                      </div>
                      <div class="card-body">
                        <div class="measurement-table modern">
                          <div class="table-header">
                            <span class="eye-col"></span>
                            <span class="header-cell">ESF</span>
                            <span class="header-cell">CIL</span>
                            <span class="header-cell">EJE</span>
                            <span class="header-cell">ADD</span>
                          </div>
                          <div class="table-row">
                            <span class="eye-label od">OD</span>
                            <input type="text" class="measurement-input" v-model="medidaVista.od.esf" placeholder="±0.00">
                            <input type="text" class="measurement-input" v-model="medidaVista.od.cil" placeholder="±0.00">
                            <input type="text" class="measurement-input" v-model="medidaVista.od.eje" placeholder="000°">
                            <input type="text" class="measurement-input" v-model="medidaVista.od.add" placeholder="+0.00">
                          </div>
                          <div class="table-row">
                            <span class="eye-label oi">OI</span>
                            <input type="text" class="measurement-input" v-model="medidaVista.oi.esf" placeholder="±0.00">
                            <input type="text" class="measurement-input" v-model="medidaVista.oi.cil" placeholder="±0.00">
                            <input type="text" class="measurement-input" v-model="medidaVista.oi.eje" placeholder="000°">
                            <input type="text" class="measurement-input" v-model="medidaVista.oi.add" placeholder="+0.00">
                          </div>
                        </div>
                        
                        <div class="additional-fields">
                          <div class="field-group">
                            <label class="field-label">ADD cerca</label>
                            <input type="text" class="measurement-input" v-model="medidaVista.addCerca" placeholder="+0.00">
                          </div>
                          <div class="field-group">
                            <label class="field-label">ADD intermedia</label>
                            <input type="text" class="measurement-input" v-model="medidaVista.addIntermedia" placeholder="+0.00">
                          </div>
                          <div class="field-group">
                            <label class="field-label">DIP (mm)</label>
                            <input type="text" class="measurement-input" v-model="medidaVista.dip" placeholder="65">
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Right Column - Lensometry & Additional -->
                  <div class="col-lg-6">
                    <!-- Lensometría 1 -->
                    <div class="measurement-card">
                      <div class="card-header">
                        <h5 class="card-title">
                          <i class="fas fa-microscope me-2 text-danger"></i>
                          Lensometría 1
                        </h5>
                      </div>
                      <div class="card-body">
                        <div class="measurement-table modern">
                          <div class="table-header">
                            <span class="eye-col"></span>
                            <span class="header-cell">ESF</span>
                            <span class="header-cell">CIL</span>
                            <span class="header-cell">EJE</span>
                            <span class="header-cell">ADD</span>
                          </div>
                          <div class="table-row">
                            <span class="eye-label od">OD</span>
                            <input type="text" class="measurement-input" v-model="lensometria1.od.esf" placeholder="±0.00">
                            <input type="text" class="measurement-input" v-model="lensometria1.od.cil" placeholder="±0.00">
                            <input type="text" class="measurement-input" v-model="lensometria1.od.eje" placeholder="000°">
                            <input type="text" class="measurement-input" v-model="lensometria1.od.add" placeholder="+0.00">
                          </div>
                          <div class="table-row">
                            <span class="eye-label oi">OI</span>
                            <input type="text" class="measurement-input" v-model="lensometria1.oi.esf" placeholder="±0.00">
                            <input type="text" class="measurement-input" v-model="lensometria1.oi.cil" placeholder="±0.00">
                            <input type="text" class="measurement-input" v-model="lensometria1.oi.eje" placeholder="000°">
                            <input type="text" class="measurement-input" v-model="lensometria1.oi.add" placeholder="+0.00">
                          </div>
                        </div>
                        <div class="lens-type-selector">
                          <label class="field-label">Tipo de lente 1</label>
                          <select class="modern-select" v-model="lensometria1.tipoLente">
                            <option value="">-- Seleccione Tipo de Lentes --</option>
                            <option value="monofocal">Monofocal</option>
                            <option value="bifocal">Bifocal</option>
                            <option value="progresivo">Progresivo</option>
                          </select>
                        </div>
                      </div>
                    </div>

                    <!-- Lensometría 2 -->
                    <div class="measurement-card">
                      <div class="card-header">
                        <h5 class="card-title">
                          <i class="fas fa-microscope me-2 text-danger"></i>
                          Lensometría 2
                        </h5>
                      </div>
                      <div class="card-body">
                        <div class="measurement-table modern">
                          <div class="table-header">
                            <span class="eye-col"></span>
                            <span class="header-cell">ESF</span>
                            <span class="header-cell">CIL</span>
                            <span class="header-cell">EJE</span>
                            <span class="header-cell">ADD</span>
                          </div>
                          <div class="table-row">
                            <span class="eye-label od">OD</span>
                            <input type="text" class="measurement-input" v-model="lensometria2.od.esf" placeholder="±0.00">
                            <input type="text" class="measurement-input" v-model="lensometria2.od.cil" placeholder="±0.00">
                            <input type="text" class="measurement-input" v-model="lensometria2.od.eje" placeholder="000°">
                            <input type="text" class="measurement-input" v-model="lensometria2.od.add" placeholder="+0.00">
                          </div>
                          <div class="table-row">
                            <span class="eye-label oi">OI</span>
                            <input type="text" class="measurement-input" v-model="lensometria2.oi.esf" placeholder="±0.00">
                            <input type="text" class="measurement-input" v-model="lensometria2.oi.cil" placeholder="±0.00">
                            <input type="text" class="measurement-input" v-model="lensometria2.oi.eje" placeholder="000°">
                            <input type="text" class="measurement-input" v-model="lensometria2.oi.add" placeholder="+0.00">
                          </div>
                        </div>
                        <div class="lens-type-selector">
                          <label class="field-label">Tipo de lente 2</label>
                          <select class="modern-select" v-model="lensometria2.tipoLente">
                            <option value="">-- Seleccione Tipo de Lentes --</option>
                            <option value="monofocal">Monofocal</option>
                            <option value="bifocal">Bifocal</option>
                            <option value="progresivo">Progresivo</option>
                          </select>
                        </div>
                      </div>
                    </div>

                    <!-- Observaciones -->
                    <div class="measurement-card">
                      <div class="card-header">
                        <h5 class="card-title">
                          <i class="fas fa-clipboard-list me-2 text-secondary"></i>
                          Observaciones
                        </h5>
                      </div>
                      <div class="card-body">
                        <textarea 
                          class="modern-textarea" 
                          v-model="observaciones" 
                          placeholder="Escriba aquí las observaciones clínicas..."
                          rows="4"
                        ></textarea>
                      </div>
                    </div>

                    <!-- Ciclopejía -->
                    <div class="measurement-card">
                      <div class="card-header">
                        <h5 class="card-title">
                          <i class="fas fa-eye-dropper me-2 text-dark"></i>
                          Ciclopejía
                        </h5>
                      </div>
                      <div class="card-body">
                        <div class="ciclopejia-selector">
                          <div class="radio-group">
                            <label class="radio-option modern">
                              <input type="radio" v-model="ciclopejia" value="SI" name="ciclopejia">
                              <span class="radio-custom"></span>
                              <span class="radio-text">SÍ</span>
                            </label>
                            <label class="radio-option modern">
                              <input type="radio" v-model="ciclopejia" value="NO" name="ciclopejia">
                              <span class="radio-custom"></span>
                              <span class="radio-text">NO</span>
                            </label>
                          </div>
                        </div>
                        
                        <div class="measurement-table modern mt-3">
                          <div class="table-header">
                            <span class="eye-col"></span>
                            <span class="header-cell">ESF</span>
                            <span class="header-cell">CIL</span>
                            <span class="header-cell">EJE</span>
                          </div>
                          <div class="table-row">
                            <span class="eye-label od">OD</span>
                            <input type="text" class="measurement-input" v-model="ciclopejiaValues.od.esf" placeholder="±0.00">
                            <input type="text" class="measurement-input" v-model="ciclopejiaValues.od.cil" placeholder="±0.00">
                            <input type="text" class="measurement-input" v-model="ciclopejiaValues.od.eje" placeholder="000°">
                          </div>
                          <div class="table-row">
                            <span class="eye-label oi">OI</span>
                            <input type="text" class="measurement-input" v-model="ciclopejiaValues.oi.esf" placeholder="±0.00">
                            <input type="text" class="measurement-input" v-model="ciclopejiaValues.oi.cil" placeholder="±0.00">
                            <input type="text" class="measurement-input" v-model="ciclopejiaValues.oi.eje" placeholder="000°">
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Action Buttons -->
                <div class="form-actions">
                  <button @click="enviarLolicli" class="btn btn-success btn-lg">
                    <i class="fas fa-paper-plane me-2"></i>
                    ENVIAR AL LOLICLI
                  </button>
                  <button @click="regresar" class="btn btn-secondary btn-lg ms-3">
                    <i class="fas fa-arrow-left me-2"></i>
                    REGRESAR
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Patient Search Modal -->
    <div v-if="showPatientModal" class="modal-overlay" @click="closePatientModal">
      <div class="modal-dialog" @click.stop>
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Buscar Historia Clínica</h5>
            <button @click="closePatientModal" class="btn-close"></button>
          </div>
          <div class="modal-body">
            <div class="search-controls">
              <div class="search-input">
                <label>Buscar:</label>
                <input 
                  type="text" 
                  v-model="patientSearch" 
                  class="form-control"
                  placeholder="Buscar paciente..."
                  @keyup.enter="searchPatients"
                >
                <button 
                  @click="searchPatients" 
                  class="btn btn-primary"
                  :disabled="isLoadingPatients"
                >
                  <i v-if="isLoadingPatients" class="fas fa-spinner fa-spin me-1"></i>
                  Buscar
                </button>
              </div>
            </div>
            <div class="patient-table">
              <div v-if="isLoadingPatients" class="text-center p-3">
                <i class="fas fa-spinner fa-spin me-2"></i>
                Buscando pacientes...
              </div>
              <div v-else-if="filteredPatients.length === 0" class="text-center p-3 text-muted">
                No se encontraron pacientes
              </div>
              <table v-else class="table table-striped">
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>HC N°</th>
                    <th>Paciente</th>
                    <th>DNI</th>
                    <th>Fecha Nacim.</th>
                    <th>Edad</th>
                    <th>Fecha Atención</th>
                    <th>Raza</th>
                    <th>Sexo</th>
                    <th>Hora Atenci.</th>
                    <th>Ocupación</th>
                    <th>Afiliación</th>
                    <th>Domicilio</th>
                    <th>Acompañante</th>
                    <th>DNI Acompa.</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="patient in filteredPatients" :key="patient.id" @click="selectPatient(patient)" class="patient-row">
                    <td>{{ patient.id }}</td>
                    <td>{{ patient.hc_num }}</td>
                    <td>{{ patient.paciente }}</td>
                    <td>{{ patient.dni }}</td>
                    <td>{{ formatDate(patient.fecha_nacimiento) }}</td>
                    <td>{{ patient.edad }}</td>
                    <td>{{ formatDate(patient.fecha_atencion) }}</td>
                    <td>{{ patient.raza }}</td>
                    <td>{{ patient.sexo }}</td>
                    <td>{{ patient.hora_atencion }}</td>
                    <td>{{ patient.ocupacion }}</td>
                    <td>{{ patient.afiliacion }}</td>
                    <td>{{ patient.domicilio }}</td>
                    <td>{{ patient.acompanante }}</td>
                    <td>{{ patient.dni_acompanante }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
            <!-- Pagination Info -->
            <div class="pagination-info mt-3">
              <p class="text-muted mb-2">
                Mostrando {{ (currentPage - 1) * perPage + 1 }} a {{ Math.min(currentPage * perPage, totalRecords) }} de {{ totalRecords }} registros
              </p>
            </div>
            <!-- Modal Actions with Pagination -->
            <div class="modal-actions">
              <button 
                @click="previousPage" 
                :disabled="currentPage <= 1 || isLoadingPatients"
                class="btn btn-secondary"
              >
                Anterior
              </button>
              <span class="pagination-current mx-3">
                Página {{ currentPage }} de {{ totalPages }}
              </span>
              <button 
                @click="nextPage" 
                :disabled="currentPage >= totalPages || isLoadingPatients"
                class="btn btn-secondary"
              >
                Siguiente
              </button>
              <button @click="closePatientModal" class="btn btn-secondary ms-3">Cancelar</button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Records Selection Modal -->
    <div v-if="showRecordsModal" class="modal-overlay" @click="closeRecordsModal">
      <div class="modal-dialog classic-records-modal" @click.stop>
        <div class="modal-content classic-modal">
          <div class="modal-header classic-header">
            <h5 class="modal-title classic-title">
              <i v-if="selectedCategory === 'lensometria'" class="fas fa-microscope me-2"></i>
              <i v-else class="fas fa-eye me-2"></i>
              {{ selectedCategory === 'lensometria' ? 'Resultados de Lensómetro' : 'Resultados Auto Kerato-refractómetro' }}
            </h5>
            <button @click="closeRecordsModal" class="classic-close-btn">×</button>
          </div>
          <div class="modal-body classic-body">
            <div v-if="isLoadingRecords" class="loading-container">
              <div class="spinner"></div>
              <p>Cargando registros...</p>
            </div>
            <div v-else-if="availableRecords.length === 0" class="no-records">
              <p>No se encontraron registros</p>
            </div>
            <div v-else class="records-container">
              <!-- Lensometer Records Table -->
              <table v-if="selectedCategory === 'lensometria'" class="classic-table">
                <thead>
                  <tr>
                    <th>serial_id</th>
                    <th>exam_id</th>
                    <th>Modelo</th>
                    <th>Creado</th>
                    <th>Medido</th>
                    <th>ESF OD</th>
                    <th>CIL OD</th>
                    <th>EJE OD</th>
                    <th>ADD OD</th>
                    <th>ESF OI</th>
                    <th>CIL OI</th>
                    <th>EJE OI</th>
                    <th>ADD OI</th>
                    <th>Acción</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="record in availableRecords" :key="record.id" class="classic-row">
                    <td>{{ record.serial_id || record.id }}</td>
                    <td>{{ record.exam_id || '-1' }}</td>
                    <td>{{ record.model || 'HUVITZ_L' }}</td>
                    <td>{{ formatDateTime(record.created_date || record.examination_date) }}</td>
                    <td>{{ formatDateTime(record.measured_date || record.examination_date) }}</td>
                    <td>{{ record.od?.esf || '+0.00' }}</td>
                    <td>{{ record.od?.cil || '+0.00' }}</td>
                    <td>{{ record.od?.eje || '000' }}</td>
                    <td>{{ record.od?.add || '' }}</td>
                    <td>{{ record.oi?.esf || '+0.00' }}</td>
                    <td>{{ record.oi?.cil || '+0.00' }}</td>
                    <td>{{ record.oi?.eje || '000' }}</td>
                    <td>{{ record.oi?.add || '' }}</td>
                    <td class="action-cell">
                      <button 
                        @click="selectRecord(record, 1)" 
                        class="classic-btn primary"
                      >
                        Usar para Lensometría 1
                      </button>
                      <button 
                        @click="selectRecord(record, 2)" 
                        class="classic-btn secondary"
                      >
                        Usar para Lensometría 2
                      </button>
                    </td>
                  </tr>
                </tbody>
              </table>

              <!-- Auto Kerato-Refractometer Records Table -->
              <table v-else class="classic-table">
                <thead>
                  <tr>
                    <th>Archivo</th>
                    <th>Fecha</th>
                    <th>Hora</th>
                    <th>ESF OD</th>
                    <th>CIL OD</th>
                    <th>EJE OD</th>
                    <th>ESF OI</th>
                    <th>CIL OI</th>
                    <th>EJE OI</th>
                    <th>K1 OD</th>
                    <th>K1 Axis OD</th>
                    <th>K2 OD</th>
                    <th>K2 Axis OD</th>
                    <th>K1 OI</th>
                    <th>K1 Axis OI</th>
                    <th>K2 OI</th>
                    <th>K2 Axis OI</th>
                    <th>DIP</th>
                    <th>Acción</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="record in availableRecords" :key="record.id" class="classic-row">
                    <td>{{ record.filename || record.id }}</td>
                    <td>{{ formatDate(record.examination_date) }}</td>
                    <td>{{ record.examination_time || '23:18:01' }}</td>
                    <td>{{ record.od?.esf || '0.50' }}</td>
                    <td>{{ record.od?.cil || '0.0' }}</td>
                    <td>{{ record.od?.eje || '0' }}</td>
                    <td>{{ record.oi?.esf || '0.50' }}</td>
                    <td>{{ record.oi?.cil || '-0.25' }}</td>
                    <td>{{ record.oi?.eje || '25' }}</td>
                    <td>{{ record.keratometry?.od?.k1 || '41.75' }}</td>
                    <td>{{ record.keratometry?.od?.k1_axis || '170' }}</td>
                    <td>{{ record.keratometry?.od?.k2 || '42.25' }}</td>
                    <td>{{ record.keratometry?.od?.k2_axis || '80' }}</td>
                    <td>{{ record.keratometry?.oi?.k1 || '42.00' }}</td>
                    <td>{{ record.keratometry?.oi?.k1_axis || '15' }}</td>
                    <td>{{ record.keratometry?.oi?.k2 || '42.75' }}</td>
                    <td>{{ record.keratometry?.oi?.k2_axis || '105' }}</td>
                    <td>{{ record.dip || '65.50' }}</td>
                    <td class="action-cell">
                      <button 
                        @click="selectRecord(record)" 
                        class="classic-btn primary"
                      >
                        Usar registro seleccionado
                      </button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

// Current step management
const currentStep = ref(1)

// Historia Clínica data
const historiaClinica = ref({
  hcNumber: '',
  paciente: '',
  dni: '',
  fechaNacimiento: '',
  edad: '',
  fechaAtencion: new Date().toISOString().split('T')[0], // Today's date
  raza: '',
  sexo: '',
  horaAtencion: '',
  ocupacion: '',
  afiliacion: '',
  domicilioActual: '',
  acompanante: '',
  dniAcompanante: ''
})

// Agudeza Visual / Refracción data
const selectedCategory = ref('')
const examinationDate = ref(new Date().toISOString().split('T')[0]) // Default to today

const agudezaVisual = ref({
  od: { avsc: '', avcc: '', ae: '' },
  oi: { avsc: '', avcc: '', ae: '' }
})

const refraccionAutomatica = ref({
  od: { esf: '', cil: '', eje: '' },
  oi: { esf: '', cil: '', eje: '' },
  dip: ''
})

const queratometria = ref({
  od: { valor1: '', cil1: '' },
  oi: { valor1: '', cil1: '' }
})

const lensometria1 = ref({
  od: { esf: '', cil: '', eje: '', add: '' },
  oi: { esf: '', cil: '', eje: '', add: '' },
  tipoLente: ''
})

const lensometria2 = ref({
  od: { esf: '', cil: '', eje: '', add: '' },
  oi: { esf: '', cil: '', eje: '', add: '' },
  tipoLente: ''
})

const medidaVista = ref({
  od: { esf: '', cil: '', eje: '', add: '' },
  oi: { esf: '', cil: '', eje: '', add: '' },
  addCerca: '',
  addIntermedia: '',
  dip: ''
})

const observaciones = ref('')
const ciclopejia = ref('')
const ciclopejiaValues = ref({
  od: { esf: '', cil: '', eje: '' },
  oi: { esf: '', cil: '', eje: '' }
})

// Patient search modal
const showPatientModal = ref(false)
const patientSearch = ref('')
const patients = ref([])
const isLoadingPatients = ref(false)
const currentPage = ref(1)
const totalPages = ref(1)
const totalRecords = ref(0)
const perPage = ref(20)

// Records consultation
const isLoadingRecords = ref(false)
const availableRecords = ref([])
const showRecordsModal = ref(false)

// Computed properties
const canProceedToStep2 = computed(() => {
  return historiaClinica.value.paciente && historiaClinica.value.dni
})

const filteredPatients = computed(() => {
  // Since filtering is now handled on the backend, just return the patients
  return patients.value
})

// Watch for birth date changes to calculate age automatically
watch(() => historiaClinica.value.fechaNacimiento, (newDate) => {
  if (newDate) {
    const birthDate = new Date(newDate)
    const today = new Date()
    let age = today.getFullYear() - birthDate.getFullYear()
    const monthDiff = today.getMonth() - birthDate.getMonth()
    
    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
      age--
    }
    
    historiaClinica.value.edad = age >= 0 ? age : 0
  }
})

// Utility function to format dates
function formatDate(dateString) {
  if (!dateString) return ''
  
  // Handle different date formats that might come from the API
  let date
  if (dateString.includes('T')) {
    // If it's an ISO string with time
    date = new Date(dateString)
  } else if (dateString.match(/^\d{4}-\d{2}-\d{2}$/)) {
    // If it's YYYY-MM-DD format
    date = new Date(dateString + 'T00:00:00')
  } else {
    date = new Date(dateString)
  }
  
  // Check if date is valid
  if (isNaN(date.getTime())) return dateString
  
  // Format as DD/MM/YYYY
  const day = date.getDate().toString().padStart(2, '0')
  const month = (date.getMonth() + 1).toString().padStart(2, '0')
  const year = date.getFullYear()
  
  return `${day}/${month}/${year}`
}

// Utility function to format date and time
function formatDateTime(dateString) {
  if (!dateString) return ''
  
  let date
  if (dateString.includes('T')) {
    date = new Date(dateString)
  } else if (dateString.match(/^\d{4}-\d{2}-\d{2}$/)) {
    date = new Date(dateString + 'T00:00:00')
  } else {
    date = new Date(dateString)
  }
  
  if (isNaN(date.getTime())) return dateString
  
  // Format as YYYY-MM-DD HH:mm:ss
  const year = date.getFullYear()
  const month = (date.getMonth() + 1).toString().padStart(2, '0')
  const day = date.getDate().toString().padStart(2, '0')
  const hours = date.getHours().toString().padStart(2, '0')
  const minutes = date.getMinutes().toString().padStart(2, '0')
  const seconds = date.getSeconds().toString().padStart(2, '0')
  
  return `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`
}

// Utility function to format dates for form inputs (YYYY-MM-DD)
function formatDateForInput(dateString) {
  if (!dateString) return ''
  
  // Handle different date formats that might come from the API
  let date
  if (dateString.includes('T')) {
    // If it's an ISO string with time, just take the date part
    return dateString.split('T')[0]
  } else if (dateString.match(/^\d{4}-\d{2}-\d{2}$/)) {
    // If it's already YYYY-MM-DD format, return as is
    return dateString
  } else {
    date = new Date(dateString)
    if (isNaN(date.getTime())) return dateString
    
    // Format as YYYY-MM-DD
    const year = date.getFullYear()
    const month = (date.getMonth() + 1).toString().padStart(2, '0')
    const day = date.getDate().toString().padStart(2, '0')
    
    return `${year}-${month}-${day}`
  }
}

// API Methods
async function loadPatients(page = 1) {
  isLoadingPatients.value = true
  try {
    const response = await axios.get('/api/search-patients', {
      params: { 
        search: patientSearch.value,
        page: page,
        per_page: perPage.value
      }
    })
    
    // Handle paginated response
    patients.value = response.data.data || response.data
    currentPage.value = response.data.current_page || 1
    totalPages.value = response.data.last_page || 1
    totalRecords.value = response.data.total || patients.value.length
  } catch (error) {
    console.error('Error loading patients:', error)
    patients.value = []
    currentPage.value = 1
    totalPages.value = 1
    totalRecords.value = 0
  } finally {
    isLoadingPatients.value = false
  }
}

async function searchPatients() {
  currentPage.value = 1 // Reset to first page when searching
  await loadPatients(1)
}

// Pagination methods
async function goToPage(page) {
  if (page >= 1 && page <= totalPages.value) {
    await loadPatients(page)
  }
}

async function previousPage() {
  if (currentPage.value > 1) {
    await loadPatients(currentPage.value - 1)
  }
}

async function nextPage() {
  if (currentPage.value < totalPages.value) {
    await loadPatients(currentPage.value + 1)
  }
}

// Records consultation methods
async function consultarRegistros() {
  if (!selectedCategory.value) return
  
  isLoadingRecords.value = true
  availableRecords.value = []
  
  try {
    let response
    
    console.log('Base URL:', window.axios.defaults.baseURL)
    
    if (selectedCategory.value === 'lensometria') {
      // Query PostgreSQL lensmeterresulttbl table - get all available records
      console.log('Making request to:', `${window.axios.defaults.baseURL}/api/lensometer-records`)
      response = await axios.get('/api/lensometer-records', {
        params: {
          // Don't filter by patient_id or date - show all available records
          limit: 100 // Limit to prevent too many records
        }
      })
    } else if (selectedCategory.value === 'refraccion') {
      // Query XML files from shared folder \\127.0.0.1\kr - get all available records
      console.log('Making request to:', `${window.axios.defaults.baseURL}/api/refraction-records`)
      response = await axios.get('/api/refraction-records', {
        params: {
          // Don't filter by patient_id or date - show all available records
          limit: 100 // Limit to prevent too many records
        }
      })
    }
    
    if (response && response.data) {
      availableRecords.value = response.data
      
      // Always show modal regardless of number of records
      showRecordsModal.value = true
    }
  } catch (error) {
    console.error(`Error loading ${selectedCategory.value} records:`, error)
    console.error('Request URL:', error.config?.url)
    console.error('Base URL:', error.config?.baseURL)
    alert(`Error al consultar registros de ${selectedCategory.value}. Por favor, verifique la conexión.`)
  } finally {
    isLoadingRecords.value = false
  }
}

function closeRecordsModal() {
  showRecordsModal.value = false
}

function selectRecord(record, lensometryNumber = null) {
  if (selectedCategory.value === 'lensometria') {
    loadLensometerData(record, lensometryNumber)
  } else if (selectedCategory.value === 'refraccion') {
    loadRefractionData(record)
  }
  closeRecordsModal()
}

function loadLensometerData(record, lensometryNumber) {
  const targetLensometry = lensometryNumber === 2 ? lensometria2 : lensometria1
  
  // Load data into the specified lensometry section
  if (record.od) {
    targetLensometry.value.od.esf = record.od.esf || ''
    targetLensometry.value.od.cil = record.od.cil || ''
    targetLensometry.value.od.eje = record.od.eje || ''
    targetLensometry.value.od.add = record.od.add || ''
  }
  
  if (record.oi) {
    targetLensometry.value.oi.esf = record.oi.esf || ''
    targetLensometry.value.oi.cil = record.oi.cil || ''
    targetLensometry.value.oi.eje = record.oi.eje || ''
    targetLensometry.value.oi.add = record.oi.add || ''
  }
  
  targetLensometry.value.tipoLente = record.lens_type || ''
  
  const lensometryName = lensometryNumber === 2 ? 'Lensometría 2' : 'Lensometría 1'
  alert(`Datos cargados exitosamente en ${lensometryName}.`)
}

function loadRefractionData(record) {
  // Load data into refraccionAutomatica
  if (record.od) {
    refraccionAutomatica.value.od.esf = record.od.esf || ''
    refraccionAutomatica.value.od.cil = record.od.cil || ''
    refraccionAutomatica.value.od.eje = record.od.eje || ''
  }
  
  if (record.oi) {
    refraccionAutomatica.value.oi.esf = record.oi.esf || ''
    refraccionAutomatica.value.oi.cil = record.oi.cil || ''
    refraccionAutomatica.value.oi.eje = record.oi.eje || ''
  }
  
  refraccionAutomatica.value.dip = record.dip || ''
  
  // Also load keratometry data if available
  if (record.keratometry) {
    if (record.keratometry.od) {
      queratometria.value.od.valor1 = record.keratometry.od.k1 || ''
      queratometria.value.od.cil1 = record.keratometry.od.cil || ''
    }
    
    if (record.keratometry.oi) {
      queratometria.value.oi.valor1 = record.keratometry.oi.k1 || ''
      queratometria.value.oi.cil1 = record.keratometry.oi.cil || ''
    }
  }
  
  alert('Datos de refracción automática cargados exitosamente.')
}

// Methods
async function buscarPaciente() {
  showPatientModal.value = true
  await loadPatients() // Load initial patient data
}

function closePatientModal() {
  showPatientModal.value = false
}

function selectPatient(patient) {
  // Map database fields to form fields
  historiaClinica.value = {
    hcNumber: patient.hc_num || '',
    paciente: patient.paciente || '',
    dni: patient.dni || '',
    fechaNacimiento: formatDateForInput(patient.fecha_nacimiento) || '',
    edad: patient.edad || '',
    fechaAtencion: formatDateForInput(patient.fecha_atencion) || '',
    raza: patient.raza || '',
    sexo: patient.sexo || '',
    horaAtencion: patient.hora_atencion || '',
    ocupacion: patient.ocupacion || '',
    afiliacion: patient.afiliacion || '',
    domicilioActual: patient.domicilio || '',
    acompanante: patient.acompanante || '',
    dniAcompanante: patient.dni_acompanante || ''
  }
  closePatientModal()
}

function revisarAgudezaVisual() {
  if (canProceedToStep2.value) {
    currentStep.value = 2
  }
}

function regresar() {
  currentStep.value = 1
}

function enviarLolicli() {
  // Handle form submission
  console.log('Enviando al LOLICLI:', {
    historiaClinica: historiaClinica.value,
    agudezaVisual: agudezaVisual.value,
    refraccionAutomatica: refraccionAutomatica.value,
    queratometria: queratometria.value,
    lensometria1: lensometria1.value,
    medidaVista: medidaVista.value,
    observaciones: observaciones.value,
    ciclopejia: ciclopejia.value,
    ciclopejiaValues: ciclopejiaValues.value
  })
  
  // Could redirect or show success message
  alert('Datos enviados correctamente al LOLICLI')
}

onMounted(() => {
  // Fields start completely blank - no auto-initialization
})
</script>

<style scoped>
.registros-optometricos {
  min-height: 100vh;
  background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
  padding: 0;
}

/* Progress Steps */
.steps-header {
  background: white;
  border-bottom: 2px solid #eaeaea;
  padding: 2rem 0;
}

.progress-steps {
  display: flex;
  justify-content: center;
  align-items: center;
  max-width: 600px;
  margin: 0 auto;
}

.step {
  display: flex;
  flex-direction: column;
  align-items: center;
  color: #6c757d;
  transition: all 0.3s ease;
}

.step.active {
  color: #009fe3;
}

.step.completed {
  color: #28a745;
}

.step-circle {
  width: 60px;
  height: 60px;
  border-radius: 50%;
  background: #e9ecef;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  margin-bottom: 0.5rem;
  transition: all 0.3s ease;
}

.step.active .step-circle {
  background: #009fe3;
  color: white;
}

.step.completed .step-circle {
  background: #28a745;
  color: white;
}

.step-label {
  font-weight: 600;
  text-align: center;
  font-size: 0.9rem;
}

.step-line {
  width: 100px;
  height: 2px;
  background: #e9ecef;
  margin: 0 2rem;
  transition: all 0.3s ease;
}

.step-line.active {
  background: #009fe3;
}

/* Form Content */
.step-content {
  padding: 2rem 0;
}

.form-card {
  background: white;
  border-radius: 12px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  overflow: hidden;
}

.form-header {
  background: linear-gradient(135deg, #009fe3, #007bb5);
  color: white;
  padding: 1.5rem 2rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.form-title {
  margin: 0;
  font-size: 1.5rem;
  font-weight: 600;
}

.hc-number {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.hc-number label {
  margin: 0;
  font-weight: 600;
}

.hc-input {
  width: 120px;
  background: rgba(255, 255, 255, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.3);
  color: white;
}

.hc-input::placeholder {
  color: rgba(255, 255, 255, 0.7);
}

.category-selector {
  display: flex;
  align-items: center;
}

.select-wrapper {
  position: relative;
  display: inline-block;
}

.category-selector .form-select {
  width: 300px;
  background: rgba(255, 255, 255, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.3);
  color: white;
  appearance: none;
  -webkit-appearance: none;
  -moz-appearance: none;
  padding-right: 2.5rem;
}

.category-selector .form-select option {
  background: #2c3e50;
  color: white;
}

.select-caret {
  position: absolute;
  right: 0.75rem;
  top: 50%;
  transform: translateY(-50%);
  color: rgba(255, 255, 255, 0.7);
  pointer-events: none;
  font-size: 0.875rem;
}

.form-body {
  padding: 2rem;
}

.form-label {
  font-weight: 600;
  margin-bottom: 0.5rem;
  color: #2c3e50;
}

.form-control, .form-select {
  border: 1px solid #ddd;
  border-radius: 6px;
  padding: 0.5rem 0.75rem;
}

.form-control:focus, .form-select:focus {
  border-color: #009fe3;
  box-shadow: 0 0 0 0.2rem rgba(0, 159, 227, 0.25);
}

.form-check-container {
  display: flex;
  gap: 1rem;
  align-items: center;
  margin-top: 0.5rem;
}

.form-check {
  display: flex;
  align-items: center;
  gap: 0.25rem;
}

.form-actions {
  margin-top: 2rem;
  text-align: right;
}

.btn-lg {
  padding: 0.75rem 2rem;
  font-weight: 600;
  border-radius: 8px;
}

/* Modern Card-Based Step 2 Styles */

/* Measurement Cards */
.measurement-card {
  background: #ffffff;
  border: 1px solid #e1e5e9;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
  margin-bottom: 24px;
  transition: all 0.3s ease;
}

.measurement-card:hover {
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
  border-color: #c1c7cd;
}

.measurement-card .card-header {
  background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
  border-bottom: 1px solid #e1e5e9;
  border-radius: 12px 12px 0 0;
  padding: 16px 20px;
}

.measurement-card .card-title {
  margin: 0;
  font-size: 16px;
  font-weight: 600;
  color: #2c3e50;
  display: flex;
  align-items: center;
}

.measurement-card .card-body {
  padding: 20px;
}

/* Modern Measurement Tables */
.measurement-table.modern {
  background: #ffffff;
  border: 1px solid #e1e5e9;
  border-radius: 8px;
  overflow: hidden;
}

.measurement-table.modern .table-header {
  background: linear-gradient(135deg, #495057 0%, #343a40 100%);
  color: white;
  display: grid;
  grid-template-columns: 60px repeat(auto-fit, minmax(80px, 1fr));
  gap: 1px;
  padding: 0;
  font-weight: 600;
  font-size: 13px;
}

.measurement-table.modern .header-cell,
.measurement-table.modern .eye-col {
  padding: 12px 8px;
  text-align: center;
  display: flex;
  align-items: center;
  justify-content: center;
}

.measurement-table.modern .table-row {
  display: grid;
  grid-template-columns: 60px repeat(auto-fit, minmax(80px, 1fr));
  gap: 1px;
  background: #e1e5e9;
}

.measurement-table.modern .table-row:nth-child(even) {
  background: #f8f9fa;
}

/* Eye Labels */
.eye-label {
  background: #f8f9fa;
  color: #2c3e50;
  font-weight: 700;
  font-size: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 12px 8px;
  border-right: 1px solid #e1e5e9;
}

.eye-label.od {
  background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
  color: #1565c0;
}

.eye-label.oi {
  background: linear-gradient(135deg, #f3e5f5 0%, #e1bee7 100%);
  color: #7b1fa2;
}

/* Modern Input Fields */
.measurement-input {
  background: #ffffff;
  border: 1px solid #ced4da;
  border-radius: 6px;
  padding: 10px 12px;
  font-size: 14px;
  font-weight: 500;
  color: #495057;
  text-align: center;
  transition: all 0.2s ease;
  width: 100%;
}

.measurement-input:focus {
  border-color: #007bff;
  box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
  outline: none;
  background: #f8f9ff;
}

.measurement-input:hover {
  border-color: #adb5bd;
}

.measurement-input::placeholder {
  color: #9ca3af;
  font-weight: 400;
}

.measurement-input.single {
  max-width: 120px;
  margin: 0 auto;
}

/* Additional Fields */
.additional-field {
  margin-top: 16px;
  display: flex;
  align-items: center;
  gap: 12px;
}

.additional-fields {
  margin-top: 16px;
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
  gap: 16px;
}

.field-group {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.field-label {
  font-size: 13px;
  font-weight: 600;
  color: #495057;
  margin: 0;
}

/* Keratometry Table */
.keratometry-table {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.kera-row {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px;
  background: #f8f9fa;
  border-radius: 8px;
  border: 1px solid #e1e5e9;
}

.kera-fields {
  display: flex;
  align-items: center;
  gap: 8px;
  flex: 1;
}

.field-separator {
  font-size: 13px;
  font-weight: 600;
  color: #6c757d;
  padding: 0 8px;
}

/* Lens Type Selector */
.lens-type-selector {
  margin-top: 16px;
}

.modern-select {
  background: #ffffff;
  border: 1px solid #ced4da;
  border-radius: 6px;
  padding: 10px 12px;
  font-size: 14px;
  color: #495057;
  width: 100%;
  transition: all 0.2s ease;
  appearance: none;
  background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e");
  background-position: right 8px center;
  background-repeat: no-repeat;
  background-size: 16px;
  padding-right: 40px;
}

.modern-select:focus {
  border-color: #007bff;
  box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
  outline: none;
}

/* Modern Textarea */
.modern-textarea {
  background: #ffffff;
  border: 1px solid #ced4da;
  border-radius: 8px;
  padding: 12px 16px;
  font-size: 14px;
  color: #495057;
  width: 100%;
  resize: vertical;
  min-height: 100px;
  transition: all 0.2s ease;
}

.modern-textarea:focus {
  border-color: #007bff;
  box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
  outline: none;
  background: #f8f9ff;
}

.modern-textarea::placeholder {
  color: #9ca3af;
  font-style: italic;
}

/* Ciclopejía Selector */
.ciclopejia-selector {
  margin-bottom: 16px;
}

.radio-group {
  display: flex;
  gap: 20px;
  align-items: center;
}

.radio-option.modern {
  display: flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
  padding: 8px 16px;
  border-radius: 6px;
  transition: all 0.2s ease;
}

.radio-option.modern:hover {
  background: #f8f9fa;
}

.radio-option.modern input[type="radio"] {
  display: none;
}

.radio-custom {
  width: 18px;
  height: 18px;
  border: 2px solid #ced4da;
  border-radius: 50%;
  position: relative;
  transition: all 0.2s ease;
}

.radio-option.modern input[type="radio"]:checked + .radio-custom {
  border-color: #007bff;
  background: #007bff;
}

.radio-option.modern input[type="radio"]:checked + .radio-custom::after {
  content: '';
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  width: 8px;
  height: 8px;
  background: white;
  border-radius: 50%;
}

.radio-text {
  font-size: 14px;
  font-weight: 600;
  color: #495057;
}

/* Form Actions Enhanced */
.form-actions {
  margin-top: 40px;
  padding-top: 24px;
  border-top: 2px solid #e9ecef;
  display: flex;
  justify-content: flex-end;
  gap: 16px;
}

.form-actions .btn {
  font-weight: 600;
  font-size: 15px;
  padding: 12px 24px;
  border-radius: 8px;
  transition: all 0.3s ease;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.form-actions .btn-success {
  background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
  border: none;
  box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
}

.form-actions .btn-success:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(40, 167, 69, 0.4);
}

.form-actions .btn-secondary {
  background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
  border: none;
  box-shadow: 0 4px 12px rgba(108, 117, 125, 0.3);
}

.form-actions .btn-secondary:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(108, 117, 125, 0.4);
}

/* Date Input Styling */
.date-input {
  position: relative;
  background: #ffffff;
  border: 1px solid #ced4da;
  border-radius: 6px;
  padding: 10px 12px;
  font-size: 14px;
  color: #495057;
  transition: all 0.2s ease;
}

.date-input:focus {
  border-color: #007bff;
  box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
  outline: none;
  background: #f8f9ff;
}

.date-input:hover {
  border-color: #adb5bd;
}

/* Custom date picker icon */
.date-input::-webkit-calendar-picker-indicator {
  background: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='%23007bff' d='M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM2 4v10a1 1 0 0 0 1 1h10a1 1 0 0 0 1-1V4H2z'/%3e%3c/svg%3e") no-repeat;
  background-size: 16px 16px;
  cursor: pointer;
}

.date-input::-webkit-calendar-picker-indicator:hover {
  opacity: 0.8;
}

/* Enhanced Form Labels */
.form-label {
  font-weight: 600;
  color: #2c3e50;
  margin-bottom: 6px;
  font-size: 14px;
}

/* Header Controls Layout */
.header-controls {
  display: flex;
  align-items: center;
  gap: 20px;
  flex-wrap: wrap;
}

.examination-date {
  display: flex;
  align-items: center;
  gap: 8px;
}

.date-label {
  font-weight: 600;
  color: #2c3e50;
  font-size: 14px;
  white-space: nowrap;
}

.examination-date-input {
  min-width: 160px;
  font-size: 14px;
  padding: 8px 12px;
}

.category-selector {
  display: flex;
  align-items: center;
  gap: 12px;
  flex: 1;
}

@media (max-width: 768px) {
  .header-controls {
    flex-direction: column;
    align-items: stretch;
    gap: 15px;
  }
  
  .examination-date {
    justify-content: space-between;
  }
  
  .category-selector {
    flex-direction: column;
    gap: 10px;
  }
}

/* Enhanced Form Controls */
.form-control {
  border: 1px solid #ced4da;
  border-radius: 6px;
  padding: 10px 12px;
  font-size: 14px;
  transition: all 0.2s ease;
}

.form-control:focus {
  border-color: #007bff;
  box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
}

.form-control[readonly] {
  background-color: #f8f9fa;
  color: #6c757d;
}

/* Sex Radio Buttons Enhancement */
.form-check-container {
  display: flex;
  gap: 20px;
  align-items: center;
  margin-top: 8px;
}

.form-check {
  display: flex;
  align-items: center;
  gap: 6px;
}

.form-check-input {
  width: 18px;
  height: 18px;
  margin: 0;
}

.form-check-label {
  font-weight: 600;
  color: #495057;
  cursor: pointer;
  margin: 0;
}

/* Responsive Design */
@media (max-width: 992px) {
  .measurement-table.modern .table-header,
  .measurement-table.modern .table-row {
    grid-template-columns: 50px repeat(auto-fit, minmax(70px, 1fr));
  }
  
  .measurement-input {
    padding: 8px 10px;
    font-size: 13px;
  }
  
  .additional-fields {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 768px) {
  .measurement-card .card-body {
    padding: 16px;
  }
  
  .measurement-card .card-header {
    padding: 12px 16px;
  }
  
  .form-actions {
    flex-direction: column;
  }
  
  .form-actions .btn {
    width: 100%;
  }
}

/* Classic Records Modal Styles - Match Java Application */
.classic-records-modal {
  max-width: 90%;
  width: 1200px;
}

.classic-modal {
  border: 2px solid #333;
  border-radius: 0;
  box-shadow: 2px 2px 10px rgba(0, 0, 0, 0.5);
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.classic-header {
  background: linear-gradient(to bottom, #f0f0f0, #d0d0d0);
  border-bottom: 1px solid #999;
  padding: 8px 12px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-radius: 0;
}

.classic-title {
  font-size: 13px;
  font-weight: bold;
  color: #333;
  margin: 0;
}

.classic-close-btn {
  background: none;
  border: none;
  font-size: 16px;
  font-weight: bold;
  color: #666;
  cursor: pointer;
  padding: 0;
  width: 20px;
  height: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.classic-close-btn:hover {
  color: #000;
  background: #e0e0e0;
}

.classic-body {
  padding: 8px;
  background: white;
  max-height: 70vh;
  overflow: auto;
}

.records-container {
  overflow: auto;
  max-height: 60vh;
}

.classic-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 11px;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  background: white;
}

.classic-table th {
  background: linear-gradient(to bottom, #f8f8f8, #e8e8e8);
  border: 1px solid #ccc;
  padding: 4px 6px;
  text-align: center;
  font-weight: bold;
  color: #333;
  font-size: 11px;
  white-space: nowrap;
}

.classic-table td {
  border: 1px solid #ddd;
  padding: 2px 4px;
  text-align: center;
  font-size: 11px;
  white-space: nowrap;
  background: white;
}

.classic-row:nth-child(even) {
  background: #f9f9f9;
}

.classic-row:hover {
  background: #e6f3ff !important;
}

.action-cell {
  padding: 2px !important;
}

.classic-btn {
  font-size: 10px;
  padding: 2px 6px;
  margin: 1px;
  border: 1px solid #ccc;
  border-radius: 2px;
  cursor: pointer;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  white-space: nowrap;
  min-width: 80px;
}

.classic-btn.primary {
  background: linear-gradient(to bottom, #e8f4fd, #b6d7f0);
  color: #0066cc;
  border-color: #7eb4ea;
}

.classic-btn.primary:hover {
  background: linear-gradient(to bottom, #d0e8fc, #9ac7ed);
  border-color: #5094d4;
}

.classic-btn.secondary {
  background: linear-gradient(to bottom, #f8f8f8, #e8e8e8);
  color: #333;
  border-color: #ccc;
}

.classic-btn.secondary:hover {
  background: linear-gradient(to bottom, #f0f0f0, #d8d8d8);
  border-color: #999;
}

.loading-container {
  text-align: center;
  padding: 40px;
  color: #666;
}

.spinner {
  width: 30px;
  height: 30px;
  border: 3px solid #f3f3f3;
  border-top: 3px solid #0066cc;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin: 0 auto 10px;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.no-records {
  text-align: center;
  padding: 40px;
  color: #666;
  font-size: 13px;
}

/* Override any conflicting Bootstrap styles */
.classic-records-modal .modal-content {
  border: 2px solid #333 !important;
  border-radius: 0 !important;
}

.classic-records-modal .table {
  margin: 0 !important;
}

.classic-records-modal .btn {
  all: unset;
  display: inline-block;
  font-size: 10px;
  padding: 2px 6px;
  margin: 1px;
  border: 1px solid #ccc;
  border-radius: 2px;
  cursor: pointer;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  white-space: nowrap;
  min-width: 80px;
  text-align: center;
  background: linear-gradient(to bottom, #e8f4fd, #b6d7f0);
  color: #0066cc;
  border-color: #7eb4ea;
}

.classic-records-modal .btn:hover {
  background: linear-gradient(to bottom, #d0e8fc, #9ac7ed);
  border-color: #5094d4;
}

/* Loading state for Consultar Registros button */
.btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn .fa-spinner {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

/* Category selector enhancement */
.category-selector .form-select:disabled {
  background-color: rgba(255, 255, 255, 0.1);
  opacity: 0.7;
}

/* Header controls responsive improvements */
@media (max-width: 992px) {
  .header-controls {
    flex-direction: column;
    gap: 12px;
  }
  
  .category-selector {
    width: 100%;
  }
  
  .category-selector .form-select {
    width: 100%;
  }
}

.dip-field label, .add-field label {
  font-weight: 600;
  min-width: 60px;
}

.dip-unit {
  color: #6c757d;
  font-size: 0.9rem;
}

.lens-type {
  margin-top: 1rem;
}

.lens-type label {
  font-weight: 600;
  margin-bottom: 0.5rem;
  display: block;
}

.add-fields {
  display: flex;
  gap: 1rem;
  margin-top: 1rem;
}

.additional-fields {
  display: grid;
  gap: 1.5rem;
}

.observations textarea {
  resize: vertical;
  min-height: 100px;
}

.ciclopejia label {
  font-weight: 600;
  margin-bottom: 0.5rem;
  display: block;
}

/* Modal Styles */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1050;
}

.modal-dialog {
  width: 90%;
  max-width: 1200px;
  max-height: 90vh;
  overflow-y: auto;
}

.modal-content {
  background: white;
  border-radius: 8px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.modal-header {
  background: #009fe3;
  color: white;
  padding: 1rem 1.5rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.modal-title {
  margin: 0;
  font-weight: 600;
}

.btn-close {
  background: none;
  border: none;
  color: white;
  font-size: 1.5rem;
  cursor: pointer;
}

.modal-body {
  padding: 1.5rem;
}

.search-controls {
  margin-bottom: 1.5rem;
}

.search-input {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.search-input label {
  font-weight: 600;
  min-width: 60px;
}

.search-input input {
  flex: 1;
}

.patient-table {
  max-height: 400px;
  overflow-y: auto;
  margin-bottom: 1rem;
}

.table {
  margin: 0;
}

.patient-row {
  cursor: pointer;
  transition: background-color 0.2s;
}

.patient-row:hover {
  background-color: #f8f9fa;
}

.modal-actions {
  display: flex;
  gap: 0.5rem;
  justify-content: flex-end;
  align-items: center;
}

.pagination-info {
  border-top: 1px solid #dee2e6;
  padding-top: 1rem;
  text-align: center;
}

.pagination-current {
  font-weight: 600;
  color: #495057;
}

/* Responsive Design */
@media (max-width: 768px) {
  .progress-steps {
    flex-direction: column;
    gap: 1rem;
  }
  
  .step-line {
    width: 2px;
    height: 50px;
    margin: 1rem 0;
  }
  
  .form-header {
    flex-direction: column;
    gap: 1rem;
    text-align: center;
  }
  
  .category-selector {
    flex-direction: column;
    gap: 0.5rem;
  }
  
  .category-selector .form-select {
    width: 100%;
  }
  
  .select-wrapper {
    width: 100%;
  }
  
  .add-fields {
    flex-direction: column;
  }
  
  .search-input {
    flex-direction: column;
    align-items: stretch;
  }
  
  .modal-actions {
    flex-wrap: wrap;
  }
}

@media (max-width: 576px) {
  .vision-grid, .refraction-grid, .lensometry-grid, .vista-grid, .ciclopejia-grid {
    grid-template-columns: 1fr;
    text-align: center;
  }
  
  .keratometry-grid {
    grid-template-columns: 1fr;
  }
  
  .form-actions {
    display: flex;
    flex-direction: column;
    gap: 1rem;
  }
}
</style>
