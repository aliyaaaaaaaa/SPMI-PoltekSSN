<template>
  <div class="container mt-4">
    <h2>Daftar Kegiatan</h2>

    <!-- Filter Section -->
    <div class="card mb-4">
      <div class="card-body">
        <h5>Filter Data</h5>
        <div class="row">
          <div class="col-md-4">
            <div class="mb-3">
              <label class="form-label">Tahun</label>
              <select v-model="selectedTahun" class="form-control" @change="fetchData">
                <option value="">Semua Tahun</option>
                <option v-for="tahun in tahunList" :key="tahun.tahun_id" :value="tahun.tahun_id">
                  {{ tahun.nama }}
                </option>
              </select>
            </div>
          </div>
          <div class="col-md-4">
            <div class="mb-3">
              <label class="form-label">Lembaga Akreditasi</label>
              <select v-model="selectedLA" class="form-control" @change="fetchData">
                <option value="">Semua LA</option>
                <option v-for="la in laList" :key="la.la_id" :value="la.la_id">
                  {{ la.nama }}
                </option>
              </select>
            </div>
          </div>
          <div class="col-md-4">
            <div class="mb-3">
              <label class="form-label">Program Studi</label>
              <select v-model="selectedProdi" class="form-control" @change="fetchData">
                <option value="">Semua Prodi</option>
                <option v-for="prodi in prodiList" :key="prodi.prodi_id" :value="prodi.prodi_id">
                  {{ prodi.nama }}
                </option>
              </select>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Daftar Standar -->
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Daftar Standar Mutu</h5>
        <div class="d-flex align-items-center">
          <button class="btn btn-light me-2" @click="expandAll">Expand all</button>
          <button class="btn btn-light" @click="collapseAll">Collapse all</button>
        </div>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col d-flex align-items-center">
            <!-- Tambah Standar: Muncul jika semua filter kosong-->
            <button 
              v-if="(!selectedTahun && !selectedLA && !selectedProdi) "
              class="btn btn-success btn-sm ms-2" 
              title="Tambah Standar" 
              @click="handleAdd('standar', null)"
            >
              <i class="fas fa-plus"></i> Tambah Standar
            </button>
          </div>
          <div class="col text-end">
            <h6>Indikator</h6>
          </div>
        </div>

        <!-- Tampilan ketika daftar standar kosong -->
        <div v-if="standarList.length === 0" class="text-center py-5">
          <div v-if="isFiltered" class="alert alert-warning mb-4">
            Tidak ada standar mutu untuk kombinasi filter yang dipilih
          </div>
          <div v-else-if="selectedTahun || selectedLA || selectedProdi" class="alert alert-warning mb-4">
            Silakan lengkapi Filter Data untuk menampilkan Standar Mutu!
          </div>
          <button 
            v-if="isFiltered"
            class="btn btn-primary"
            @click="handleGenerateStandar"
          >
            <i class="fas fa-sync-alt me-2"></i>
            Generate Standar Mutu
          </button>
        </div>

        <!-- Daftar standar (tetap seperti sebelumnya) -->
        <div v-else>
          <!-- Standar Level -->
          <div v-for="standar in standarList" :key="standar.standar_id" class="standar-group">
            <div class="d-flex justify-content-between align-items-center standar-item">
              <div @click="toggle('standar', standar.standar_id)">
                <i :class="isExpanded('standar', standar.standar_id) ? 'fas fa-caret-down' : 'fas fa-caret-right'"></i>
                {{ standar.nama }}
              </div>
              <div class="action-buttons">
                <button class="btn btn-success me-1" title="Tambah PSM" @click="handleAdd('psm', standar)">
                  <i class="fas fa-plus"></i> Tambah PSM
                </button>
                <button class="btn btn-warning me-1" title="Edit Standar" @click="handleEdit('standar', standar)">
                  <i class="fas fa-edit"></i> Edit
                </button>
                <!-- Tombol hapus Standar -->
                <button 
                  class="btn btn-danger" 
                  title="Hapus Standar" 
                  @click="handleDelete('standar', standar)"
                >
                  <i class="fas fa-trash"></i> Hapus
                </button>
              </div>
            </div>

            <!-- PSM Level -->
            <div v-if="isExpanded('standar', standar.standar_id)" class="ps-4">
              <div v-for="psm in standar.psm" :key="psm.psm_id" class="psm-group">
                <div class="d-flex justify-content-between align-items-center psm-item">
                  <div @click="toggle('psm', psm.psm_id)">
                    <i :class="isExpanded('psm', psm.psm_id) ? 'fas fa-caret-down' : 'fas fa-caret-right'"></i>
                    {{ psm.nama }}
                  </div>
                  <div class="action-buttons">
                    <button class="btn btn-success me-1" title="Tambah SSM" @click="handleAdd('ssm', psm)">
                      <i class="fas fa-plus"></i> Tambah SSM
                    </button>
                    <button class="btn btn-warning me-1" title="Edit PSM" @click="handleEdit('psm', psm)">
                      <i class="fas fa-edit"></i> Edit
                    </button>
                    <button class="btn btn-danger" title="Hapus PSM" @click="handleDelete('psm', psm)">
                      <i class="fas fa-trash"></i> Hapus
                    </button>
                  </div>
                </div>

                <!-- SSM Level -->
                <div v-if="isExpanded('psm', psm.psm_id)" class="ps-4">
                  <div v-for="ssm in psm.ssm" :key="ssm.ssm_id" class="ssm-group">
                    <div class="d-flex justify-content-between align-items-center ssm-item">
                      <div @click="toggle('ssm', ssm.ssm_id)">
                        <i :class="isExpanded('ssm', ssm.ssm_id) ? 'fas fa-caret-down' : 'fas fa-caret-right'"></i>
                        {{ ssm.nama }}
                      </div>
                      <div class="action-buttons">
                        <button class="btn btn-success me-1" title="Tambah Sub SSM" @click="handleAdd('subssm', ssm)">
                          <i class="fas fa-plus"></i> Tambah Sub SSM
                        </button>
                        <button class="btn btn-warning me-1" title="Edit SSM" @click="handleEdit('ssm', ssm)">
                          <i class="fas fa-edit"></i> Edit
                        </button>
                        <button class="btn btn-danger" title="Hapus SSM" @click="handleDelete('ssm', ssm)">
                          <i class="fas fa-trash"></i> Hapus
                        </button>
                      </div>
                    </div>

                    <!-- SubSSM Level -->
                    <div v-if="isExpanded('ssm', ssm.ssm_id)" class="ps-4">
                      <div v-for="subssm in ssm.subssm" :key="subssm.subssm_id" class="d-flex justify-content-between align-items-center subssm-item">
                        <div>{{ subssm.nama }}</div>
                        <div class="action-buttons">
                          <button 
                            class="btn btn-warning me-1" 
                            title="Edit Sub SSM" 
                            @click.stop="handleEdit('subssm', subssm)"
                          >
                            <i class="fas fa-edit"></i> Edit
                          </button>
                          <button 
                            class="btn btn-danger" 
                            title="Hapus Sub SSM" 
                            @click.stop="handleDelete('subssm', subssm)"
                          >
                            <i class="fas fa-trash"></i> Hapus
                          </button>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Form -->
    <div class="modal" tabindex="-1" :class="{ 'd-block': showModal }">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ getModalTitle }}</h5>
            <button type="button" class="btn-close" @click="closeModal"></button>
          </div>
          <form @submit.prevent="handleSubmit">
            <div class="modal-body"></div>
              <!-- Form khusus untuk SubSSM -->
              <div v-if="currentAction === 'subssm'">
                <div class="mb-3">
                  <label class="form-label">Parent Standar Mutu</label>
                  <input type="text" :value="getParentPSM" class="form-control bg-light" readonly>
                </div>

                <div class="mb-3">
                  <label class="form-label">Kategori</label>
                  <input type="text" value="SubSSM" class="form-control bg-light" readonly>
                </div>

                <div class="mb-3">
                  <label class="form-label">Nama Standar</label>
                  <input type="text" v-model="formData.nama" class="form-control" required :readonly="isEditing && currentOnId" :class="{'bg-light': isEditing && currentOnId}">
                </div>

                <div class="mb-3">
                  <label class="form-label">Data Dukung</label>
                  <input type="text" v-model="formData.data_dukung" class="form-control">
                </div>

                <div class="col-md-6 mb-3">
                  <label class="form-label">Bobot Nilai</label>
                  <input type="number" v-model="formData.bobot_nilai" class="form-control" step="0.01" min="0" max="100">
                </div>

                <div class="mb-3">
                  <label class="form-label">Keterangan</label>
                  <textarea v-model="formData.keterangan" class="form-control" rows="3"></textarea>
                </div>
                <!-- Jenis Indikator -->
                <div class="mb-3">
                  <label class="form-label">Jenis Indikator</label>
                  <select v-model="formData.jenis_indikator" class="form-select" required>
                    <option value="">Pilih Jenis</option>
                    <option value="Kualitatif">Kualitatif</option>
                    <option value="Kuantitatif">Kuantitatif</option>
                  </select>
                </div>
                <div class="mb-3">
                  <label class="form-label">Nilai Mutu & Keterangan</label>
                  <div v-for="(nm, index) in nilaimutuList" :key="nm.nm_id" class="row g-3 mb-2 align-items-center">
                    <!-- Nilai (readonly) -->
                    <div class="col-2">
                      <input type="text" class="form-control" :value="nm.nilai" readonly>
                    </div>
                    
                    <!-- Keterangan -->
                    <div class="col-10">
                      <input type="text" v-model="nm.keterangan" class="form-control" 
                            placeholder="Keterangan untuk nilai mutu ini">
                    </div>
                  </div>
                  
                </div>
              </div>

              <!-- Form untuk SSM -->
              <div v-else-if="currentAction === 'ssm'">
                <div class="mb-3">
                  <label class="form-label">Parent Standar Mutu</label>
                  <input type="text" :value="currentParent?.nama" class="form-control bg-light" readonly>
                </div>

                <div class="mb-3">
                  <label class="form-label">Kategori</label>
                  <input type="text" value="SSM" class="form-control bg-light" readonly>
                </div>

                <div class="mb-3">
                  <label class="form-label">Nama Standar</label>
                  <input type="text" v-model="formData.nama" class="form-control" required :readonly="isEditing && currentOnId" :class="{'bg-light': isEditing && currentOnId}">
                </div>

                <div class="mb-3">
                  <label class="form-label">Data Dukung</label>
                  <input type="text" v-model="formData.data_dukung" class="form-control">
                </div>

                <div class="mb-3">
                  <label class="form-label">Keterangan</label>
                  <textarea v-model="formData.keterangan" class="form-control" rows="3"></textarea>
                </div>

                <div class="row">
                  <div class="col-md-6 mb-3">
                    <label class="form-label">Jenis Indikator</label>
                    <select v-model="formData.jenis_indikator" class="form-control" required>
                      <option value="">Pilih Jenis</option>
                      <option v-for="indikator in indikatorList" :key="indikator.indikator_id" :value="indikator.indikator_id">
                        {{ indikator.nama }}
                      </option>
                    </select>
                  </div>
                  <div class="col-md-6 mb-3">
                    <label class="form-label">Bobot Nilai</label>
                    <input type="number" v-model="formData.bobot_nilai" class="form-control" step="0.01" min="0" max="100">
                  </div>
                </div>
              </div>

              <!-- Form untuk PSM -->
              <div v-else-if="currentAction === 'psm'">
                <div class="mb-3">
                  <label class="form-label">Parent Standar Mutu</label>
                  <input type="text" :value="currentParent?.nama" class="form-control bg-light" readonly>
                </div>

                <div class="mb-3">
                  <label class="form-label">Kategori</label>
                  <input type="text" value="PSM" class="form-control bg-light" readonly>
                </div>

                <div class="mb-3">
                  <label class="form-label">Nama Standar</label>
                  <input type="text" v-model="formData.nama" class="form-control" required :readonly="isEditing && currentOnId" :class="{'bg-light': isEditing && currentOnId}">
                </div>

                <div class="mb-3">
                  <label class="form-label">Data Dukung</label>
                  <input type="text" v-model="formData.data_dukung" class="form-control">
                </div>

                <div class="mb-3">
                  <label class="form-label">Keterangan</label>
                  <textarea v-model="formData.keterangan" class="form-control" rows="3"></textarea>
                </div>

                <div class="row">
                  <div class="col-md-6 mb-3">
                    <label class="form-label">Jenis Indikator</label>
                    <select v-model="formData.jenis_indikator" class="form-control" required>
                      <option value="">Pilih Jenis</option>
                      <option v-for="indikator in indikatorList" :key="indikator.indikator_id" :value="indikator.indikator_id">
                        {{ indikator.nama }}
                      </option>
                    </select>
                  </div>
                  <div class="col-md-6 mb-3">
                    <label class="form-label">Bobot Nilai</label>
                    <input type="number" v-model="formData.bobot_nilai" class="form-control" step="0.01" min="0" max="100">
                  </div>
                </div>
              </div>

              <!-- Form untuk Standar -->
              <div v-else-if="currentAction === 'standar'">
                <div class="mb-3">
                  <label class="form-label">Kategori</label>
                  <input type="text" value="Standar" class="form-control bg-light" readonly>
                </div>

                <div class="mb-3">
                  <label class="form-label">Nama Standar</label>
                  <!-- Filter ON: dropdown -->
                  <select v-if="isEditing && isFiltered"
                          v-model="formData.standar_id"
                          class="form-control"
                          required>
                    <option v-for="std in standarListAll" :key="std.standar_id" :value="std.standar_id">
                      {{ std.nama }}
                    </option>
                  </select>
                  <!-- Filter OFF: input text -->
                  <input v-else
                         type="text"
                         v-model="formData.nama"
                         class="form-control"
                         required>
                </div>

                <div class="mb-3">
                  <label class="form-label">Data Dukung</label>
                  <input type="text" v-model="formData.data_dukung" class="form-control">
                </div>

                <div class="mb-3">
                  <label class="form-label">Keterangan</label>
                  <textarea v-model="formData.keterangan" class="form-control" rows="3"></textarea>
                </div>

                <div class="row">
                  <div class="col-md-6 mb-3">
                    <label class="form-label">Jenis Indikator</label>
                    <select v-model="formData.jenis_indikator" class="form-control" required>
                      <option value="">Pilih Jenis</option>
                      <option v-for="indikator in indikatorList" :key="indikator.indikator_id" :value="indikator.indikator_id">
                        {{ indikator.nama }}
                      </option>
                    </select>
                  </div>
                  <div class="col-md-6 mb-3">
                    <label class="form-label">Bobot Nilai</label>
                    <input type="number" v-model="formData.bobot_nilai" class="form-control" step="0.01" min="0" max="100">
                  </div>
                </div>
              </div>

              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" @click="closeModal">Batal</button>
                <button type="submit" class="btn btn-primary">{{ isEditing ? 'Update' : 'Simpan' }}</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Generate Standar -->
    <div class="modal" tabindex="-1" :class="{ 'd-block': showGenerateModal }">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Generate Standar Mutu</h5>
            <button type="button" class="btn-close" @click="closeGenerateModal"></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="handleGenerateSubmit">
              <!-- Alert untuk pesan validasi -->
              <div v-if="validationMessage" 
                   :class="`alert alert-${validationStatus} mb-3`">
                {{ validationMessage }}
              </div>

              <!-- Pilih LA -->
              <div class="mb-3">
                <label class="form-label">Lembaga Akreditasi</label>
                <select v-model="generateFormData.selectedLA" class="form-select">
                  <option value="">-- PILIH --</option>
                  <option v-for="la in laList" :key="la.la_id" :value="la.la_id">
                    {{ la.nama }}
                  </option>
                </select>
              </div>

              <!-- Pilih Tahun -->
              <div class="mb-3">
                <label class="form-label">Tahun</label>
                <select v-model="generateFormData.selectedTahun" class="form-select">
                  <option value="">-- PILIH --</option>
                  <option v-for="tahun in tahunList" :key="tahun.tahun_id" :value="tahun.tahun_id">
                    {{ tahun.nama }}
                  </option>
                </select>
              </div>

              <!-- Pilih Prodi -->
              <div class="mb-3">
                <label class="form-label">Program Studi</label>
                <select v-model="generateFormData.selectedProdi" class="form-select">
                  <option value="">-- PILIH --</option>
                  <option v-for="prodi in prodiList" :key="prodi.prodi_id" :value="prodi.prodi_id">
                    {{ prodi.nama }}
                  </option>
                </select>
              </div>

              <!-- Pilih Standar -->
              <div class="mb-3">
                <label class="form-label">Standar</label>
                <select v-model="generateFormData.selectedStandar" class="form-select">
                  <option value="">-- PILIH --</option>
                  <option v-for="standar in standarListAll" :key="standar.standar_id" :value="standar.standar_id">
                    {{ standar.nama }}<span v-if="prevStandarIds.includes(Number(standar.standar_id))"> (Tahun Sebelumnya)</span>
                  </option>
                </select>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" @click="closeGenerateModal">Batal</button>
                <button type="submit" class="btn btn-primary">Generate</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

    <!-- Warning Modal -->
    <div class="modal" tabindex="-1" :class="{ 'd-block': showWarningModal }">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Peringatan</h5>
            <button type="button" class="btn-close" @click="showWarningModal = false"></button>
          </div>
          <div class="modal-body">
            <p>{{ warningMessage }}</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="showWarningModal = false">Batal</button>
            <button type="button" class="btn btn-primary" @click="handleWarningProceed">
              Lanjutkan
            </button>
          </div>
        </div>
      </div>
    </div>
</template>

<script>
export default {
  name: 'DaftarKegiatan',
  data() {
    return {
      standarList: [],
      expandedItems: {
        standar: new Set(),
        psm: new Set(),
        ssm: new Set()
      },
      selectedTahun: '',
      selectedLA: '',
      selectedProdi: '',
      showModal: false,
      isEditing: false,
      currentAction: null,
      currentParent: null,
      formData: {
        nama: '',
        keterangan: '',
        data_dukung: '',
        jenis_indikator: '',
        bobot_nilai: '',
        standar_id: ''
      },
      tahunList: [],
      laList: [],
      prodiList: [],
      indikatorList: [],
      showGenerateModal: false,
      generateFormData: {
        selectedLA: '',
        selectedTahun: '',
        selectedProdi: '',
        selectedStandar: ''
      },
      validationMessage: '',
      validationStatus: '',
      standarListAll: [],
      prevStandarIds: [],
      currentOnId: null,
      showWarningModal: false,
      warningMessage: '',
      oldData: {},
      bypassWarning: false,
      deleteContext: null,
      updateContext: null,
      addContext: null,
      _pendingSubmit: false,
      warningConfirmCallback: null,
      nilaimutuList: []
    }
  },
  computed: {
    getModalTitle() {
      const action = this.isEditing ? 'Edit' : 'Tambah'
      const type = this.currentAction?.charAt(0).toUpperCase() + this.currentAction?.slice(1)
      return `${action} ${type}`
    },
    getParentPSM() {
      if (this.currentAction === 'subssm' && this.currentParent) {
        // currentParent adalah SSM, ambil nama SSM
        return this.currentParent.nama || '';
      }
      return '';
    },
    isFiltered() {
      return this.selectedTahun && this.selectedLA && this.selectedProdi
    }
  },
  methods: {
    async fetchTahun() {
      try {
        const response = await fetch('/api/kegiatan.php?action=get_tahun')
        const data = await response.json()
        this.tahunList = data
      } catch (error) {
        console.error('Error fetching tahun:', error)
      }
    },
    async fetchLA() {
      try {
        const response = await fetch('/api/kegiatan.php?action=get_la')
        const data = await response.json()
        this.laList = data
      } catch (error) {
        console.error('Error fetching LA:', error)
      }
    },
    async fetchProdi() {
      try {
        const response = await fetch('/api/kegiatan.php?action=get_prodi')
        const data = await response.json()
        this.prodiList = data
      } catch (error) {
        console.error('Error fetching prodi:', error)
      }
    },
    async fetchIndikator() {
      try {
        const response = await fetch('/api/kegiatan.php?action=get_indikator')
        const data = await response.json()
        this.indikatorList = data
      } catch (error) {
        console.error('Error fetching indikator:', error)
      }
    },
    async fetchAllStandar() {
      try {
        const response = await fetch('/api/kegiatan.php?action=get_standar')
        const data = await response.json()
        this.standarListAll = data
      } catch (error) {
        console.error('Error fetching standar:', error)
      }
    },
    async fetchPrevStandar() {
      if (
        this.generateFormData.selectedLA &&
        this.generateFormData.selectedTahun &&
        this.generateFormData.selectedProdi
      ) {
        const la_id = this.generateFormData.selectedLA
        const tahun_id = this.generateFormData.selectedTahun
        const prodi_id = this.generateFormData.selectedProdi
        const response = await fetch(`/api/kegiatan.php?action=get_prev_standar&la_id=${la_id}&tahun_id=${tahun_id}&prodi_id=${prodi_id}`)
        this.prevStandarIds = (await response.json()).map(Number)
      } else {
        this.prevStandarIds = []
      }
    },
    async fetchData() {
      try {
        const params = new URLSearchParams({
          tahun_id: this.selectedTahun || '',
          la_id: this.selectedLA || '',
          prodi_id: this.selectedProdi || ''
        })
        
        const response = await fetch(`/api/kegiatan.php?action=get_hirarki&${params}`)
        const data = await response.json()
        this.standarList = data

        // Get on_id if all filters are selected
        if (this.isFiltered) {
          const onResponse = await fetch(`/api/kegiatan.php?action=get_on_id&${params}`)
          const onData = await onResponse.json()
          this.currentOnId = onData.on_id
        } else {
          this.currentOnId = null
        }
      } catch (error) {
        console.error('Error fetching data:', error)
      }
    },
    toggle(type, id) {
      if (this.expandedItems[type].has(id)) {
        this.expandedItems[type].delete(id)
      } else {
        this.expandedItems[type].add(id)
      }
    },
    isExpanded(type, id) {
      return this.expandedItems[type].has(id)
    },
    expandAll() {
      this.standarList.forEach(standar => {
        this.expandedItems.standar.add(standar.standar_id)
        standar.psm.forEach(psm => {
          this.expandedItems.psm.add(psm.psm_id)
          psm.ssm.forEach(ssm => {
            this.expandedItems.ssm.add(ssm.ssm_id)
          })
        })
      })
    },
    collapseAll() {
      this.expandedItems.standar.clear()
      this.expandedItems.psm.clear()
      this.expandedItems.ssm.clear()
    },
    handleAdd(type, parent) {
      this.isEditing = false
      this.currentAction = type
      this.currentParent = parent
      
      // Reset formData dengan semua field yang diperlukan
      this.formData = {
        nama: '',
        keterangan: '',
        data_dukung: '',
        jenis_indikator: '',
        bobot_nilai: '',
        standar_id: ''
      }

      // Jika menambah psm, set standar_id dari parent
      if (type === 'psm' && parent) {
        this.formData.standar_id = parent.standar_id
      }
      
      this.showModal = true
    },
    handleEdit(type, item) {
      this.isEditing = true
      this.currentAction = type
      if (type === 'subssm') {
        for (const standar of this.standarList) {
          for (const psm of standar.psm) {
            const foundSSM = psm.ssm.find(ssm => ssm.subssm.some(subssm => subssm.subssm_id === item.subssm_id))
            if (foundSSM) {
              this.currentParent = foundSSM
              break
            }
          }
        }
        
        this.formData = {
          subssm_id: item.subssm_id,
          nama: item.nama,
          keterangan: item.keterangan || '',
          data_dukung: item.data_dukung || '',
          jenis_indikator: item.indikator_id || '',
          bobot_nilai: item.bobot_nilai || '',
          ssm_id: this.currentParent?.ssm_id
        }
        this.oldData = {
          nama: item.nama,
          keterangan: item.keterangan || '',
          data_dukung: item.data_dukung || '',
          bobot_nilai: item.bobot_nilai || '',
          jenis_indikator: item.indikator_id || ''
        }
      } else if (type === 'ssm') {
        for (const standar of this.standarList) {
          for (const psm of standar.psm) {
            if (psm.ssm.some(s => s.ssm_id === item.ssm_id)) {
              this.currentParent = psm
              break
            }
          }
        }
        
        this.formData = {
          ssm_id: item.ssm_id,
          nama: item.nama,
          keterangan: item.keterangan || '',
          data_dukung: item.data_dukung || '',
          jenis_indikator: item.indikator_id || '',
          bobot_nilai: item.bobot_nilai || '',
          psm_id: this.currentParent?.psm_id
        }
        this.oldData = {
          nama: item.nama,
          keterangan: item.keterangan || '',
          data_dukung: item.data_dukung || '',
          bobot_nilai: item.bobot_nilai || '',
          jenis_indikator: item.indikator_id || ''
        }
      } else if (type === 'psm') {
        for (const standar of this.standarList) {
          if (standar.psm.some(p => p.psm_id === item.psm_id)) {
            this.currentParent = standar
            break
          }
        }
        this.formData = {
          psm_id: item.psm_id,
          nama: item.nama,
          keterangan: item.keterangan || '',
          data_dukung: item.data_dukung || '',
          jenis_indikator: item.indikator_id || '',
          bobot_nilai: item.bobot_nilai || '',
          standar_id: this.currentParent?.standar_id
        }
        this.oldData = {
          nama: item.nama,
          keterangan: item.keterangan || '',
          data_dukung: item.data_dukung || '',
          bobot_nilai: item.bobot_nilai || '',
          jenis_indikator: item.indikator_id || ''
        }
      } else if (type === 'standar') {
        this.currentParent = null
        if (this.isFiltered) {
          this.formData = {
            standar_id: item.standar_id,
            nama: item.nama,
            keterangan: item.keterangan || '',
            data_dukung: item.data_dukung || '',
            jenis_indikator: item.indikator_id || '',
            bobot_nilai: item.bobot_nilai || ''
          }
          this.oldData = {
            standar_id: item.standar_id,
            nama: item.nama,
            keterangan: item.keterangan || '',
            data_dukung: item.data_dukung || '',
            bobot_nilai: item.bobot_nilai || '',
            jenis_indikator: item.indikator_id || ''
          }
        } else {
          this.formData = {
            standar_id: item.standar_id,
            nama: item.nama,
            keterangan: item.keterangan || '',
            data_dukung: item.data_dukung || '',
            jenis_indikator: item.indikator_id || '',
            bobot_nilai: item.bobot_nilai || ''
          }
          this.oldData = {
            nama: item.nama,
            keterangan: item.keterangan || '',
            data_dukung: item.data_dukung || '',
            bobot_nilai: item.bobot_nilai || '',
            jenis_indikator: item.indikator_id || ''
          }
        }
      }
      this.showModal = true
    },
    async handleDelete(type, item) {
      console.log('handleDelete', type, item);
      const validTypes = ['psm', 'ssm', 'subssm', 'standar'];
      if (!type || !validTypes.includes(type)) {
        console.error('Tipe penghapusan tidak valid! type:', type, 'item:', item);
        alert('Tipe penghapusan tidak valid!');
        return;
      }
      // --- Warning SEMUA TIPE, baik FILTER ON maupun OFF ---
      if (['standar', 'psm', 'ssm', 'subssm'].includes(type) && !this.bypassWarning) {
        let namaStandarInduk = '';
        if (type === 'standar') {
          namaStandarInduk = item.nama;
        } else if (type === 'psm') {
          for (const standar of this.standarList) {
            if (standar.psm.some(p => p.psm_id === item.psm_id)) {
              namaStandarInduk = standar.nama;
              break;
            }
          }
        } else if (type === 'ssm') {
          for (const standar of this.standarList) {
            for (const psm of standar.psm) {
              if (psm.ssm.some(s => s.ssm_id === item.ssm_id)) {
                namaStandarInduk = standar.nama;
                break;
              }
            }
            if (namaStandarInduk) break;
          }
        } else if (type === 'subssm') {
          for (const standar of this.standarList) {
            for (const psm of standar.psm) {
              for (const ssm of psm.ssm) {
                if (ssm.subssm.some(sub => sub.subssm_id === item.subssm_id)) {
                  namaStandarInduk = standar.nama;
                  break;
                }
              }
              if (namaStandarInduk) break;
            }
            if (namaStandarInduk) break;
          }
        }
        let tipe = type.toUpperCase();
        let nama = item.nama;
        let pesan = '';
        if (this.isFiltered) {
          // FILTER ON (soft delete)
          const prodi = this.prodiList.find(p => p.prodi_id == this.selectedProdi)?.nama || '';
          const la = this.laList.find(l => l.la_id == this.selectedLA)?.nama || '';
          const tahun = this.tahunList.find(t => t.tahun_id == this.selectedTahun)?.nama || '';
          pesan = `PERHATIAN! Penghapusan ini akan diterapkan pada ${tipe} ${nama} untuk prodi ${prodi} pada lembaga akreditasi ${la} tahun ${tahun}. Apakah Anda yakin ingin melanjutkan?`;
        } else {
          // FILTER OFF (hard delete)
          pesan = `PERHATIAN! Penghapusan ini akan diterapkan pada template standar ${namaStandarInduk} seluruh tahun akreditasi dan program studi terkait! Apakah Anda yakin ingin melanjutkan?`;
        }
        this.warningMessage = pesan;
        this.showWarningModal = true;
        this.bypassWarning = true;
        this.deleteContext = { type, item };
        this.updateContext = null;
        this.addContext = null;
        return;
      }
      let payload = { username: 'current_user' };
      if (type === 'psm') payload.psm_id = item.psm_id;
      if (type === 'ssm') payload.ssm_id = item.ssm_id;
      if (type === 'subssm') payload.subssm_id = item.subssm_id;
      if (type === 'standar') payload.standar_id = item.standar_id;
      // Kirim on_id jika filter aktif
      if (this.isFiltered && this.currentOnId) {
        payload.on_id = this.currentOnId;
      }
      console.log('handleDelete payload', payload);
      try {
        const response = await fetch(`/api/kegiatan.php?action=delete_${type}`, {
          method: 'DELETE',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(payload)
        });
        const result = await response.json();
        console.log('Delete result:', result);
        if (result.success) {
          this.fetchData();
          alert('Data berhasil dihapus!');
        } else {
          console.error(result.error);
          alert(result.error || 'Gagal menghapus data!');
        }
      } catch (error) {
        console.error('Error deleting item:', error);
        alert('Terjadi kesalahan saat menghapus data');
      }
      this.bypassWarning = false;
      this.deleteContext = null;
      this.updateContext = null;
      this.addContext = null;
      this.closeModal();
    },
    async handleSubmit() {
      if (!this.currentAction || !['psm','ssm','subssm','standar'].includes(this.currentAction)) {
        alert('Tipe aksi tidak valid!');
        return;
      }
      try {
        let url = this.isEditing 
          ? `/api/kegiatan.php?action=update_${this.currentAction}`
          : `/api/kegiatan.php?action=add_${this.currentAction}`
        // Buat payload dasar
        const formDataToSend = {
          ...this.formData,
          username: 'current_user',
        }
        if (this.currentAction === 'subssm') {
          // Validasi nilai mutu
          for (const nm of this.nilaimutuList) {
            if (!nm.jenis) {
              alert('Jenis indikator harus diisi untuk semua nilai mutu!');
              return;
            }
          }
          formDataToSend.indikatorArr = this.nilaimutuList.map(nm => ({
            nm_id: nm.nm_id,
            jenis: nm.jenis,
            keterangan: nm.keterangan,
            on_id: this.currentOnId
          }));
        }
        // Pastikan standar_id dikirim saat tambah psm
        if (this.currentAction === 'psm') {
          formDataToSend.standar_id = this.formData.standar_id || (this.currentParent && this.currentParent.standar_id) || ''
        }
        // --- Tambahan: Warning untuk FILTER OFF (semua tipe, tambah/edit) ---
        if (!this.bypassWarning && !this.isFiltered && ['psm', 'ssm', 'subssm', 'standar'].includes(this.currentAction)) {
          let namaStandarInduk = '';
          if (this.currentAction === 'standar') {
            namaStandarInduk = this.formData.nama;
          } else if (this.currentAction === 'psm') {
            for (const standar of this.standarList) {
              if (standar.psm.some(p => p.psm_id === this.formData.psm_id || p.psm_id === this.formData.standar_id || p.psm_id === this.currentParent?.psm_id)) {
                namaStandarInduk = standar.nama;
                break;
              }
            }
            if (!namaStandarInduk && this.currentParent && this.currentParent.nama) namaStandarInduk = this.currentParent.nama;
          } else if (this.currentAction === 'ssm') {
            for (const standar of this.standarList) {
              for (const psm of standar.psm) {
                if (psm.ssm.some(s => s.ssm_id === this.formData.ssm_id || s.ssm_id === this.currentParent?.ssm_id)) {
                  namaStandarInduk = standar.nama;
                  break;
                }
              }
              if (namaStandarInduk) break;
            }
            if (!namaStandarInduk && this.currentParent && this.currentParent.nama) namaStandarInduk = this.currentParent.nama;
          } else if (this.currentAction === 'subssm') {
            for (const standar of this.standarList) {
              for (const psm of standar.psm) {
                for (const ssm of psm.ssm) {
                  if (ssm.subssm.some(sub => sub.subssm_id === this.formData.subssm_id || sub.subssm_id === this.currentParent?.subssm_id)) {
                    namaStandarInduk = standar.nama;
                    break;
                  }
                }
                if (namaStandarInduk) break;
              }
              if (namaStandarInduk) break;
            }
            if (!namaStandarInduk && this.currentParent && this.currentParent.nama) namaStandarInduk = this.currentParent.nama;
          }
          this.warningMessage = `PERHATIAN! Perubahan/Penambahan ini akan diterapkan pada template standar ${namaStandarInduk} seluruh tahun akreditasi dan program studi terkait! Apakah Anda yakin ingin melanjutkan?`;
          this.showWarningModal = true;
          this.bypassWarning = true;
          // Simpan context agar setelah klik lanjutkan, proses submit langsung jalan
          this.updateContext = null;
          this.addContext = null;
          this.deleteContext = null;
          this._pendingSubmit = true;
          return;
        }
        // Jika warning sudah dilewati, hapus flag _pendingSubmit
        if (this._pendingSubmit) delete this._pendingSubmit;
        // Hanya tambahkan parent_id jika bukan standar baru
        if (this.currentAction === 'psm') {
          formDataToSend.standar_id = this.currentParent.standar_id
        } else if (this.currentAction === 'ssm') {
          formDataToSend.parent_id = this.currentParent.psm_id
        } else if (this.currentAction === 'subssm') {
          formDataToSend.parent_id = this.currentParent.ssm_id
        }
        // --- LOGIKA BARU UNTUK EDIT STANDAR DENGAN FILTER ON ---
        if (this.isEditing && this.currentAction === 'standar' && this.isFiltered) {
          // Jika standar_id berubah, update seluruh struktur bawahannya
          if (this.formData.standar_id !== this.oldData.standar_id) {
            // Kirim standar_id baru dan on_id ke backend
            const updatePayload = {
              on_id: this.currentOnId,
              standar_id: this.formData.standar_id,
              username: 'current_user'
            }
            url = '/api/kegiatan.php?action=update_standar_on'
            const response = await fetch(url, {
              method: 'PUT',
              headers: { 'Content-Type': 'application/json' },
              body: JSON.stringify(updatePayload)
            })
            if (response.ok) {
              this.closeModal();
              this.fetchData();
              alert('Standar dan seluruh bawahannya berhasil diganti!');
            }
            return;
          }
        }
        // --- LOGIKA LAMA UNTUK EDIT STANDAR (filter OFF atau tidak ganti standar) ---
        if (this.isEditing && ['psm', 'ssm', 'subssm', 'standar'].includes(this.currentAction)) {
          // Field yang bisa diubah
          const fields = ['nama', 'keterangan', 'data_dukung', 'bobot_nilai', 'jenis_indikator'];
          const changes = [];
          for (const field of fields) {
            // Untuk standar, ssm, subssm, field indikator_id di formData = jenis_indikator
            const fieldName = field === 'jenis_indikator' ? 'indikator_id' : field;
            if (this.formData[field] !== this.oldData[field]) {
              changes.push({ kolom: fieldName, newval: this.formData[field] });
            }
          }
          if (changes.length > 0) {
            // Tentukan id field
            let idField = '';
            if (this.currentAction === 'psm') idField = 'psm_id';
            else if (this.currentAction === 'ssm') idField = 'ssm_id';
            else if (this.currentAction === 'subssm') idField = 'subssm_id';
            else if (this.currentAction === 'standar') idField = 'standar_id';

            // Buat payload untuk update
            const updatePayload = {
              [idField]: this.formData[idField],
              username: 'current_user',
              changes
            };

            // Hanya tambahkan on_id jika filter aktif
            if (this.isFiltered && this.currentOnId) {
              updatePayload.on_id = this.currentOnId;
            }

            const response = await fetch(url, {
              method: 'PUT',
              headers: { 'Content-Type': 'application/json' },
              body: JSON.stringify(updatePayload)
            });

            if (response.ok) {
              // Simpan nama dan tipe sebelum closeModal()
              const tipe = this.currentAction;
              const namaItem = this.oldData.nama || this.formData.nama || 'item';
              this.closeModal();
              this.fetchData();
              alert(`Data berhasil diubah pada ${tipe} ${namaItem}!`);
            }
          }
          return;
        }

        // Untuk add
        if (this.isFiltered && this.currentOnId) {
          formDataToSend.on_id = this.currentOnId;
        }
        const response = await fetch(url, {
          method: this.isEditing ? 'PUT' : 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify(formDataToSend)
        });

        const result = await response.json();

        // Handle kasus khusus untuk PSM/SSM/SubSSM yang sudah ada
        if (!result.success && result.exists) {
          this.warningMessage = result.message;
          this.showWarningModal = true;
          this.warningConfirmCallback = async () => {
            if (this.currentAction === 'psm') {
              formDataToSend.psm_id = result.psm_id;
            } else if (this.currentAction === 'ssm') {
              formDataToSend.ssm_id = result.ssm_id;
            } else if (this.currentAction === 'subssm') {
              formDataToSend.subssm_id = result.subssm_id;
            }
            const retryResponse = await fetch(url, {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json'
              },
              body: JSON.stringify(formDataToSend)
            });
            const retryResult = await retryResponse.json();
            if (retryResult.success) {
              this.closeModal();
              this.fetchData();
              alert('Data berhasil ditambahkan!');
            } else {
              console.error(retryResult.error);
              alert(retryResult.error || 'Gagal menambahkan data!');
            }
            this.warningConfirmCallback = null;
          };
          return;
        }

        if (result.success) {
          // Simpan nama parent sebelum closeModal()
          let namaStandarInduk = '';
          if (this.currentAction === 'psm') {
            // currentParent adalah Standar
            namaStandarInduk = this.currentParent?.nama || '';
          } else if (this.currentAction === 'ssm') {
            // currentParent adalah PSM, cari parent Standar
            let parentStandar = null;
            if (this.currentParent && this.currentParent.standar_id) {
              parentStandar = this.standarList.find(s => s.standar_id === this.currentParent.standar_id);
            }
            namaStandarInduk = parentStandar?.nama || '';
          } else if (this.currentAction === 'subssm') {
            // currentParent adalah SSM, cari parent PSM lalu Standar
            let parentPSM = null;
            let parentStandar = null;
            if (this.currentParent && this.currentParent.ssm_id) {
              for (const standar of this.standarList) {
                for (const psm of standar.psm) {
                  if (psm.ssm.some(ssm => ssm.ssm_id === this.currentParent.ssm_id)) {
                    parentPSM = psm;
                    parentStandar = standar;
                    break;
                  }
                }
                if (parentStandar) break;
              }
            }
            namaStandarInduk = parentStandar?.nama || '';
          } else {
            namaStandarInduk = this.formData.nama;
          }
          this.closeModal();
          this.fetchData();
          if (this.currentAction === 'standar') {
            alert(`Standar ${namaStandarInduk} berhasil dibuat!`);
          } else {
            alert(`Data berhasil ditambahkan pada standar ${namaStandarInduk}!`);
          }
        } else {
          console.error(result.error);
          alert(result.error || 'Gagal menambahkan data!');
        }
      } catch (error) {
        console.error('Error submitting form:', error);
        alert('Terjadi kesalahan saat menyimpan data');
      }
      this.bypassWarning = false; // reset setelah submit
      this.updateContext = null;
      this.addContext = null;
      this.deleteContext = null;
    },
    closeModal() {
      this.showModal = false
      this.isEditing = false
      this.currentAction = null
      this.currentParent = null
      this.formData = {
        subssm_id: '',
        ssm_id: '',
        psm_id: '',
        standar_id: '',
        nama: '',
        keterangan: '',
        data_dukung: '',
        jenis_indikator: '',
        bobot_nilai: ''
      }
    },
    handleGenerateStandar() {
      this.fetchPrevStandar()
      this.fetchAllStandar()
      this.showGenerateModal = true
      this.validationMessage = ''
      this.validationStatus = ''
      this.generateFormData = {
        selectedLA: '',
        selectedTahun: '',
        selectedProdi: '',
        selectedStandar: ''
      }
    },
    closeGenerateModal() {
      this.showGenerateModal = false
      this.validationMessage = ''
      this.validationStatus = ''
      this.generateFormData = {
        selectedLA: '',
        selectedTahun: '',
        selectedProdi: '',
        selectedStandar: ''
      }
    },
    async handleGenerateSubmit() {
      // Validasi input
      if (this.generateFormData.selectedLA.length === 0 ||
          this.generateFormData.selectedTahun.length === 0 ||
          this.generateFormData.selectedProdi.length === 0 ||
          this.generateFormData.selectedStandar.length === 0) {
        this.validationMessage = 'Silakan pilih minimal satu LA, Tahun, dan Prodi'
        this.validationStatus = 'warning'
        return
      }

      try {
        const response = await fetch('/api/kegiatan.php?action=generate_standar', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify(this.generateFormData)
        });

        const result = await response.json();
        
        this.validationMessage = result.message;
        this.validationStatus = result.status;

        if (result.status === 'success') {
          setTimeout(() => {
            this.closeGenerateModal();
            this.fetchData();
          }, 2000);
        }
      } catch (error) {
        console.error('Error generating standar:', error);
        this.validationMessage = 'Terjadi kesalahan saat generate standar';
        this.validationStatus = 'error';
      }
    },
    handleWarningProceed() {
      this.showWarningModal = false;
      if (this.warningConfirmCallback) {
        this.warningConfirmCallback();
        this.warningConfirmCallback = null;
        return;
      }
      if (this._pendingSubmit) {
        this.bypassWarning = true;
        this.$nextTick(() => this.handleSubmit());
        return;
      }
      if (this.deleteContext) {
        const ctx = this.deleteContext;
        this.$nextTick(() => {
          this.bypassWarning = true;
          this.deleteContext = null;
          this.handleDelete(ctx.type, ctx.item);
        });
        return;
      }
      if (this.updateContext) {
        const fn = this.updateContext;
        this.updateContext = null;
        setTimeout(() => fn(), 0);
      }
      else if (this.addContext) {
        const fn = this.addContext;
        this.addContext = null;
        setTimeout(() => fn(), 0);
      }
    },

    async openSubssmModal(subssm = null) {
      this.isEditing = !!subssm;
      this.currentAction = 'subssm';
      //this.currentParent = null;
      //this.nilaimutuList = [];
      this.formData = {
        subssm_id: subssm ? subssm.subssm_id : '',
        nama: subssm ? subssm.nama : '',
        keterangan: subssm ? subssm.keterangan || '' : '',
        data_dukung: subssm ? subssm.data_dukung || '' : '',
        jenis_indikator: '',
        bobot_nilai: subssm ? subssm.bobot_nilai || '' : ''
      };

      try {
        const res = await fetch(`/api/kegiatan.php?action=get_nilaimutu_by_subssm&subssm_id=${subssm?.subssm_id || ''}&on_id=${this.currentOnId}`);
        const { nilaimutu, indikator } = await res.json();

        // Set jenis indikator dari indikator yang sudah ada (jika edit)
        if (indikator.length > 0) {
          this.formData.jenis_indikator = indikator[0].jenis;
        }
      
        // Gabungkan nilaimutu dengan indikator yang sudah ada (jika edit)
        this.nilaimutuList = nilaimutu.map(nm => {
          const existingIndicator = indikator.find(i => i.nm_id === nm.nm_id) || {};
          return {
            nm_id: nm.nm_id,
            nilai: nm.nilai,
            keterangan: existingIndicator.keterangan || ''
          };
        });
      } catch (err) {
          console.error('Error fetching nilaimutu:', err);
          this.nilaimutuList = [];
      }
      this.showModal = true;
    },

    // Submit form tambah/edit SubSSM (beserta indikator)
    async handleSubmit() {
      if (this.currentAction !== 'subssm') return;

      // Siapkan payload
      const payload = {
        ...this.formData,
        parent_id: this.currentParent ? this.currentParent.ssm_id : '',
        on_id: this.currentOnId,
        indikatorArr: this.nilaimutuList.map(nm => ({
          nm_id: nm.nm_id,
          jenis: nm.jenis,
          keterangan: nm.keterangan,
          on_id: this.currentOnId
        }))
      };

      // Tentukan URL dan method
      const url = this.isEditing
        ? '/api/kegiatan.php?action=update_subssm'
        : '/api/kegiatan.php?action=add_subssm';
      const method = this.isEditing ? 'PUT' : 'POST';

      try {
        const response = await fetch(url, {
          method,
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(payload)
        });
        const result = await response.json();
        if (result.success) {
          this.closeModal();
          this.fetchData();
          alert('Data SubSSM dan indikator berhasil disimpan!');
        } else {
          alert(result.error || 'Gagal menyimpan data!');
        }
      } catch (err) {
        alert('Terjadi kesalahan saat menyimpan data');
      }
    },
  },
  mounted() {
    this.fetchTahun()
    this.fetchLA()
    this.fetchProdi()
    this.fetchIndikator()
    this.fetchData()
    this.fetchAllStandar()
  },
  watch: {
    'generateFormData.selectedLA': 'fetchPrevStandar',
    'generateFormData.selectedTahun': 'fetchPrevStandar',
    'generateFormData.selectedProdi': 'fetchPrevStandar'
  }
}
</script>

<style scoped>
.standar-item, .psm-item, .ssm-item, .subssm-item {
  padding: 8px;
  margin: 4px 0;
  cursor: pointer;
  border-radius: 4px;
}

.standar-item:hover, .psm-item:hover, .ssm-item:hover {
  background-color: #f8f9fa;
}

.standar-item {
  font-weight: bold;
  color: #0d6efd;
}

.psm-item {
  color: #198754;
}

.ssm-item {
  color: #6c757d;
}

.subssm-item {
  color: #495057;
  cursor: default;
  font-size: 0.95em;
}

i {
  margin-right: 8px;
  width: 12px;
}

.form-select {
  width: 200px;
}

.action-buttons {
  display: none;
  gap: 0.5rem;
}

.standar-item:hover .action-buttons,
.psm-item:hover .action-buttons,
.ssm-item:hover .action-buttons,
.subssm-item:hover .action-buttons {
  display: flex;
}

.btn {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.875rem;
}

.btn i {
  font-size: 0.875rem;
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .btn {
    padding: 0.25rem 0.5rem;
  }
  
  .btn span {
    display: none; /* Hide text on mobile, show only icons */
  }
}

.modal {
  background-color: rgba(0, 0, 0, 0.5);
}

.alert {
  padding: 0.75rem 1.25rem;
  margin-bottom: 1rem;
  border: 1px solid transparent;
  border-radius: 0.25rem;
}

.alert-warning {
  color: #856404;
  background-color: #fff3cd;
  border-color: #ffeeba;
}

.py-5 {
  padding-top: 3rem;
  padding-bottom: 3rem;
}

.me-2 {
  margin-right: 0.5rem;
}

.btn-primary {
  color: #fff;
  background-color: #0d6efd;
  border-color: #0d6efd;
}

.btn-primary:hover {
  background-color: #0b5ed7;
  border-color: #0a58ca;
}
</style>