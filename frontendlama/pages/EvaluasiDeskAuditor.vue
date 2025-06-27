<template>
  <div class="container mt-4">
    <h2>Evaluasi Desk</h2>

    <!-- Filter Section -->
    <div class="card mb-4">
      <div class="card-body">
        <h5>Filter Data</h5>
        <div class="row">
          <div class="col-md-4">
            <div class="mb-3">
              <label class="form-label">Tahun</label>
              <select v-model="selectedTahun" class="form-control" @change="fetchData">
                <option value="">Pilih Tahun</option>
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
                <option value="">Pilih LA</option>
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
                <option value="">Pilih Prodi</option>
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
    <div v-if="isFiltered" class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center">
          <h5 class="mb-0">Daftar Standar Mutu</h5>
          <span v-if="jumlahBelumDinilai > 0" class="badge bg-danger ms-3">
            {{ jumlahBelumDinilai }} belum dinilai
          </span>
        </div>
        <div>
          <button class="btn btn-light me-2" @click="expandAll">Expand all</button>
          <button class="btn btn-light" @click="collapseAll">Collapse all</button>
        </div>
      </div>
      <div class="card-body">
        <div v-if="isLoading" class="text-center py-5">
          <div class="spinner-border" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
          <p class="mt-2">Memuat data...</p>
        </div>

        <div v-else-if="standarList.length === 0" class="text-center py-5">
          <div class="alert alert-warning">
            Tidak ada standar mutu untuk kombinasi filter yang dipilih.
          </div>
        </div>

        <!-- Hierarchy Display -->
        <div v-else>
          <!-- Standar Level -->
          <div v-for="standar in standarList" :key="standar.standar_id" class="standar-group">
            <div class="d-flex justify-content-between align-items-center standar-item" @click="toggle('standar', standar.standar_id)">
              <div>
                <i :class="isExpanded('standar', standar.standar_id) ? 'fas fa-caret-down' : 'fas fa-caret-right'"></i>
                {{ standar.nama }}
              </div>
            </div>

            <!-- PSM Level -->
            <div v-if="isExpanded('standar', standar.standar_id)" class="ps-4">
              <div v-for="psm in standar.psm" :key="psm.psm_id" class="psm-group">
                <div class="d-flex justify-content-between align-items-center psm-item" @click="toggle('psm', psm.psm_id)">
                   <div>
                     <i :class="isExpanded('psm', psm.psm_id) ? 'fas fa-caret-down' : 'fas fa-caret-right'"></i>
                     {{ psm.nama }}
                   </div>
                </div>

                <!-- SSM Level -->
                <div v-if="isExpanded('psm', psm.psm_id)" class="ps-4">
                  <div v-for="ssm in psm.ssm" :key="ssm.ssm_id" class="ssm-group">
                    <div class="d-flex justify-content-between align-items-center ssm-item" @click="toggle('ssm', ssm.ssm_id)">
                      <div>
                        <i :class="isExpanded('ssm', ssm.ssm_id) ? 'fas fa-caret-down' : 'fas fa-caret-right'"></i>
                        {{ ssm.nama }}
                      </div>
                    </div>
                    
                    <!-- SubSSM Level (Pilih Button) -->
                    <div v-if="isExpanded('ssm', ssm.ssm_id)" class="ps-4">
                      <div v-for="subssm in ssm.subssm" :key="subssm.subssm_id" class="subssm-item-form card my-3">
                        <div class="card-body d-flex justify-content-between align-items-center">
                          <div>
                            <p class="fw-bold mb-1">{{ subssm.nama }}</p>
                          </div>
                          <div>
                            <button class="btn btn-primary btn-sm" @click="openModal(subssm, ssm, psm, standar)">Pilih</button>
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
    </div>
     <div v-else class="text-center py-5 alert alert-info">
      Silakan pilih filter untuk melihat daftar standar mutu.
    </div>

    <!-- Modal Form -->
    <div class="modal" tabindex="-1" :class="{ 'd-block': showModal }" v-if="showModal">
      <div class="modal-dialog modal-lg"><!-- Perlebar modal -->
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Evaluasi Desk</h5>
            <button type="button" class="btn-close" @click="closeModal"></button>
          </div>
          <form @submit.prevent="handleSubmit">
            <div class="modal-body">
              <div class="mb-3">
                <div class="card bg-light border-0 mb-2">
                  <div class="card-body p-3">
                    <table class="table table-sm mb-0">
                      <tbody>
                        <tr>
                          <th class="w-50">Tahun</th>
                          <td>{{ tahunList.find(t => t.tahun_id == selectedTahun)?.nama }}</td>
                        </tr>
                        <tr>
                          <th>Lembaga Akreditasi</th>
                          <td>{{ laList.find(l => l.la_id == selectedLA)?.nama }}</td>
                        </tr>
                        <tr>
                          <th>Prodi</th>
                          <td>{{ prodiList.find(p => p.prodi_id == selectedProdi)?.nama }}</td>
                        </tr>
                        <tr>
                          <th>Standar</th>
                          <td>{{ selectedStandar?.nama }}</td>
                        </tr>
                        <tr>
                          <th>PSM</th>
                          <td>{{ selectedPSM?.nama }}</td>
                        </tr>
                        <tr>
                          <th>SSM</th>
                          <td>{{ selectedSSM?.nama }}</td>
                        </tr>
                        <tr>
                          <th>Sub SSM</th>
                          <td>{{ selectedSubssm?.nama }}</td>
                        </tr>
                        <tr>
                          <th>Hasil Evaluasi Diri</th>
                          <td>
                            <span v-if="evaluasiDiri">
                              <span class="fw-bold text-success" v-if="nilaiMutuList.find(nm => nm.nm_id == evaluasiDiri.nilai)">
                                {{ nilaiMutuList.find(nm => nm.nm_id == evaluasiDiri.nilai)?.nilai }} - {{ nilaiMutuList.find(nm => nm.nm_id == evaluasiDiri.nilai)?.keterangan }}
                              </span>
                              <span v-else class="text-muted">-</span>
                            </span>
                            <span v-else class="text-danger fw-bold">Belum Ada</span>
                          </td>
                        </tr>
                        <tr>
                          <th>Dokumen Bukti Evaluasi Diri</th>
                          <td>
                            <div v-if="buktiDokumenList.length > 0">
                              <ol class="mb-0">
                                <li v-for="doc in buktiDokumenList" :key="doc.dokumen_id">
                                  <a :href="`/uploads/${doc.path_file}`" target="_blank" rel="noopener">
                                    {{ doc.nama_file }}
                                  </a>
                                </li>
                              </ol>
                            </div>
                            <span v-else class="badge bg-danger">Tidak Ada</span>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
              <div class="mb-3">
                <label class="form-label">Nilai Evaluasi Desk</label>
                <select v-model="formData.nilai" class="form-control">
                  <option value="">Pilih Nilai</option>
                  <option v-for="nm in nilaiMutuList" :key="nm.nm_id" :value="nm.nm_id">
                    {{ nm.nilai }} - {{ nm.keterangan }}
                  </option>
                </select>
              </div>
              <div class="mb-3">
                <label class="form-label">Daftar Pertanyaan</label>
                <textarea v-model="formData.pertanyaan" class="form-control"></textarea>
              </div>
              <div class="mb-3">
                <label class="form-label">Daftar Temuan</label>
                <textarea v-model="formData.temuan" class="form-control"></textarea>
              </div>
              <div class="mb-3" v-if="formData.temuan && formData.temuan.trim() !== ''">
                <label class="form-label">Pilih Jenis Temuan</label>
                <select v-model="formData.jt_id" class="form-control">
                  <option value="">Pilih Jenis Temuan</option>
                  <option v-for="jt in jenisTemuanList" :key="jt.jt_id" :value="jt.jt_id">
                    {{ jt.nama_jt }}
                  </option>
                </select>
              </div>
              <div class="mb-3">
                <label class="form-label">Unggah Bukti Pendukung</label>
                <input type="file" @change="handleFileUpload" class="form-control" />
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" @click="closeModal">Batal</button>
              <button type="submit" class="btn btn-primary" :disabled="isSaving">Simpan</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'EvaluasiDeskAuditor',
  data() {
    return {
      standarList: [],
      expandedItems: {
        standar: new Set(),
        psm: new Set(),
        ssm: new Set(),
      },
      selectedTahun: '',
      selectedLA: '',
      selectedProdi: '',
      currentOnId: null,
      currentLatId: null,
      isLoading: false,
      tahunList: [],
      laList: [],
      prodiList: [],
      showModal: false,
      selectedSubssm: null,
      jenisTemuanList: [],
      formData: {
        nilai: '',
        pertanyaan: '',
        temuan: '',
        dokumen: null,
        jt_id: '', // Tambahkan ini
      },
      isSaving: false,
      nilaiMutuList: [],
      selectedStandar: null,
      selectedPSM: null,
      selectedSSM: null,
      evaluasiDiri: null,
      buktiDokumenList: [],
    };
  },
  computed: {
    isFiltered() {
      return this.selectedTahun && this.selectedLA && this.selectedProdi;
    },
    jumlahBelumDinilai() {
      // Hitung jumlah subssm yang belum ada evaluasi desk pada on_id aktif
      let count = 0;
      this.standarList.forEach(standar => {
        (standar.psm || []).forEach(psm => {
          (psm.ssm || []).forEach(ssm => {
            (ssm.subssm || []).forEach(subssm => {
              // Evaluasi desk dianggap belum dinilai jika tidak ada nilai (nm_id) pada evaluasi desk
              if (!subssm.evaluasi || !subssm.evaluasi.nilai) {
                count++;
              }
            });
          });
        });
      });
      return count;
    }
  },
  methods: {
    async fetchFilters() {
      try {
        const [tahunRes, laRes, prodiRes] = await Promise.all([
          fetch('/api/kegiatan.php?action=get_tahun'),
          fetch('/api/kegiatan.php?action=get_la'),
          fetch('/api/kegiatan.php?action=get_prodi'),
        ]);
        this.tahunList = await tahunRes.json();
        this.laList = await laRes.json();
        this.prodiList = await prodiRes.json();
      } catch (error) {
        console.error('Error fetching filters:', error);
        alert('Gagal memuat data filter.');
      }
    },
    async fetchNilai(lat_id) {
      if (!lat_id) {
        this.nilaiMutuList = [];
        return;
      }
      try {
        const response = await fetch(`/api/nilai_mutu.php?action=get_nilaiLat&lat_id=${lat_id}`);
        const data = await response.json();
        console.log('Data Nilai Mutu pada LAT tsb:', data);
        this.nilaiMutuList = data;
      } catch (error) {
        console.error('Error fetching nilai mutu:', error);
        this.nilaiMutuList = [];
      }
    },
    async fetchData() {
      if (!this.isFiltered) {
        this.standarList = [];
        this.currentOnId = null;
        return;
      }

      this.isLoading = true;
      try {
        const params = new URLSearchParams({
          tahun_id: this.selectedTahun,
          la_id: this.selectedLA,
          prodi_id: this.selectedProdi,
        });

        const onResponse = await fetch(`/api/kegiatan.php?action=get_on_id&${params}`);
        const onData = await onResponse.json();
        this.currentOnId = onData.on_id;

        if (!this.currentOnId) {
          this.standarList = [];
          alert('Objek nilai untuk filter yang dipilih tidak ditemukan.');
          this.isLoading = false;
          return;
        }

        const [hirarkiRes, evaluasiRes] = await Promise.all([
          fetch(`/api/kegiatan.php?action=get_hirarki&${params}`),
          fetch(`/api/edesk.php?action=get_evaluasi&on_id=${this.currentOnId}`),
        ]);

        const hirarkiData = await hirarkiRes.json();
        const evaluasiData = await evaluasiRes.json();
        
        hirarkiData.forEach(standar => {
          (standar.psm || []).forEach(psm => {
            (psm.ssm || []).forEach(ssm => {
              (ssm.subssm || []).forEach(subssm => {
                const evaluasi = evaluasiData[subssm.subssm_id] || {};
                subssm.evaluasi = {
                  nilai: evaluasi.nm_id || '',
                  pertanyaan: evaluasi.pertanyaan || '',
                  temuan: evaluasi.temuan || '',
                  has_dokumen: evaluasi.has_dokumen || false,
                  dokumenFile: null,
                  isSaving: false,
                };
              });
            });
          });
        });

        this.standarList = hirarkiData;
      } catch (error) {
        console.error('Error fetching data:', error);
        alert('Gagal memuat data standar atau evaluasi.');
      } finally {
        this.isLoading = false;
      }
    },
    async fetchJenisTemuan() {
      try {
        const res = await fetch('/api/jenis_temuan.php?action=get_jenis');
        this.jenisTemuanList = await res.json();
      } catch (e) {
        this.jenisTemuanList = [];
      }
    },
    async fetchBuktiDokumen(edi_id) {
      const res = await fetch(`/api/edesk.php?action=get_bukti_dokumen&edi_id=${edi_id}`);
      this.buktiDokumenList = await res.json();
    },
    async openModal(subssm, ssm, psm, standar) {
      this.selectedSubssm = subssm;
      this.selectedSSM = ssm;
      this.selectedPSM = psm;
      this.selectedStandar = standar;
      this.formData = {
        nilai: subssm.evaluasi?.nilai || '',
        pertanyaan: subssm.evaluasi?.pertanyaan || '',
        temuan: subssm.evaluasi?.temuan || '',
        dokumen: null,
        jt_id: subssm.evaluasi?.jt_id || '',
      };
      await this.fetchJenisTemuan(); // Ambil data jenis temuan saat modal dibuka
      const params = new URLSearchParams({
        tahun_id: this.selectedTahun,
        la_id: this.selectedLA,
        prodi_id: this.selectedProdi,
      });
      try {
        const onResponse = await fetch(`/api/kegiatan.php?action=get_on_id&${params}`);
        const onData = await onResponse.json();
        this.currentLatId = onData.lat_id;
        await this.fetchNilai(this.currentLatId);

        // Ambil hasil evaluasi diri dari tabel edi/edesk
        const ediRes = await fetch(`/api/edi.php?action=get_evaluasi&on_id=${onData.on_id}`);
        const ediData = await ediRes.json();
        const evalEdi = ediData[subssm.subssm_id];
        this.evaluasiDiri = evalEdi
          ? {
              nilai: evalEdi.nm_id,
              keterangan: evalEdi.keterangan || '',
              dokumen: evalEdi.has_dokumen ? true : false,
              edi_id: evalEdi.edi_id // pastikan edi_id dikirim dari backend
            }
          : null;

        // Fetch dokumen bukti jika ada edi_id
        this.buktiDokumenList = [];
        if (this.evaluasiDiri && this.evaluasiDiri.edi_id) {
          await this.fetchBuktiDokumen(this.evaluasiDiri.edi_id);
        }
      } catch (error) {
        this.nilaiMutuList = [];
        this.evaluasiDiri = null;
      }
      this.showModal = true;
    },
    closeModal() {
      this.showModal = false;
      this.selectedSubssm = null;
      this.formData = {
        nilai: '',
        pertanyaan: '',
        temuan: '',
        dokumen: null,
      };
    },
    handleFileUpload(e) {
      this.formData.dokumen = e.target.files[0];
    },
    async handleSubmit() {
      if (!this.currentOnId || !this.selectedSubssm) return;
      this.isSaving = true;
      const fd = new FormData();
      fd.append('on_id', this.currentOnId);
      fd.append('subssm_id', this.selectedSubssm.subssm_id);
      fd.append('auditor_id', 123456); // Ganti dengan id auditor login
      fd.append('nm_id', this.formData.nm_id || '');
      fd.append('pertanyaan', this.formData.pertanyaan);
      fd.append('temuan', this.formData.temuan);
      fd.append('jt_id', this.formData.jt_id || '');
      if (this.formData.dokumen) fd.append('dokumen', this.formData.dokumen);
      try {
        const response = await fetch('/api/edesk.php?action=save_evaluasi', {
          method: 'POST',
          body: fd,
        });
        const result = await response.json();
        if (!result.success) throw new Error(result.error || 'Gagal menyimpan data.');
        this.closeModal();
        this.fetchData();
      } catch (error) {
        alert(error.message);
      } finally {
        this.isSaving = false;
      }
    },
    toggle(type, id) {
      if (this.expandedItems[type].has(id)) {
        this.expandedItems[type].delete(id);
      } else {
        this.expandedItems[type].add(id);
      }
      this.$forceUpdate();
    },
    isExpanded(type, id) {
      return this.expandedItems[type].has(id);
    },
    expandAll() {
      this.standarList.forEach(standar => {
        this.expandedItems.standar.add(standar.standar_id);
        (standar.psm || []).forEach(psm => {
          this.expandedItems.psm.add(psm.psm_id);
          (psm.ssm || []).forEach(ssm => {
            this.expandedItems.ssm.add(ssm.ssm_id);
          });
        });
      });
      this.$forceUpdate();
    },
    collapseAll() {
      this.expandedItems.standar.clear();
      this.expandedItems.psm.clear();
      this.expandedItems.ssm.clear();
      this.$forceUpdate();
    },
  },
  mounted() {
    this.fetchFilters();
    this.fetchData();
  }
};
</script>

<style scoped>
.standar-item, .psm-item, .ssm-item {
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

.subssm-item-form {
  background-color: #fdfdfd;
  border-left: 3px solid #0d6efd;
}

i {
  margin-right: 8px;
  width: 12px;
}

.ps-4 {
  padding-left: 1.5rem;
}

.modal {
  background-color: rgba(0, 0, 0, 0.5);
}
</style>
