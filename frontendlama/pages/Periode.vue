<template>
  <div class="container mt-4">
    <h2>Pengaturan Periode</h2>

    <!-- Filter Section -->
    <div class="card mb-4">
      <div class="card-body">
        <h5>Filter Data</h5>
        <div class="row">
          <div class="col-md-6">
            <div class="mb-3">
              <label class="form-label">Periode</label>
              <select v-model="selectedTahun" class="form-control">
                <option value="">Semua Tahun</option>
                <option v-for="tahun in tahunList" :key="tahun.tahun_id" :value="tahun.tahun_id">
                  {{ tahun.nama }}
                </option>
              </select>
            </div>
          </div>
          <div class="col-md-6">
            <div class="mb-3">
              <label class="form-label">Lembaga Akreditasi</label>
              <select v-model="selectedLA" class="form-control">
                <option value="">Semua Lembaga Akreditasi</option>
                <option v-for="la in laList" :key="la.la_id" :value="la.la_id">
                  {{ la.nama }}
                </option>
              </select>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Search, Per page, dan Tambah -->
    <div class="d-flex justify-content-between align-items-center mb-3">
      <div class="d-flex align-items-center gap-3">
        <!-- Search Bar -->
        <div class="search-box">
          <input 
            type="text" 
            class="form-control" 
            placeholder="Cari periode..." 
            v-model="searchQuery"
          >
        </div>
        <!-- Per Page -->
        <div class="d-flex align-items-center">
          <span class="me-2">Per page:</span>
          <select class="form-select" style="width: auto;">
            <option value="10">10</option>
            <option value="25">25</option>
            <option value="50">50</option>
          </select>
        </div>
      </div>
      <!-- Tombol Tambah -->
      <button class="btn btn-primary" @click="showModal = true">
        Tambah
      </button>
    </div>

    <!-- Table -->
    <div class="table-responsive">
      <table class="table">
        <thead>
          <tr>
            <th>No</th>
            <th>Periode</th>
            <th>Lembaga Akreditasi</th>
            <th>Periode Evaluasi Diri</th>
            <th>Periode Desk Evaluation</th>
            <th>Periode Visitasi</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(item, index) in filteredList" :key="item.periode_id">
            <td>{{ index + 1 }}</td>
            <td>{{ item.tahun_nama }}</td>
            <td>{{ item.la_nama }}</td>
            <td>{{ item.periode_edisi_start }} - {{ item.periode_edisi_end }}</td>
            <td>{{ item.periode_edesk_start }} - {{ item.periode_edesk_end }}</td>
            <td>{{ item.periode_visitatif_start }} - {{ item.periode_visitatif_end }}</td>
            <td>
              <button class="btn btn-sm btn-warning me-2" @click="handleEdit(item)">
                Edit
              </button>
              <button class="btn btn-sm btn-danger" @click="handleDelete(item.periode_id)">
                Hapus
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div class="d-flex justify-content-between align-items-center mt-3">
      <div>Showing 1 to {{ filteredList.length }} of {{ filteredList.length }} entries</div>
      <nav>
        <ul class="pagination mb-0">
          <li class="page-item"><a class="page-link" href="#">Previous</a></li>
          <li class="page-item active"><a class="page-link" href="#">1</a></li>
          <li class="page-item"><a class="page-link" href="#">Next</a></li>
        </ul>
      </nav>
    </div>

    <!-- Petunjuk -->
    <div class="mt-4">
      <h5>Petunjuk</h5>
      <ul>
        <li>Data Tahun dengan status Aktif pada tahun tidak dapat dihapus.</li>
      </ul>
    </div>

    <!-- Modal Form -->
    <div class="modal" tabindex="-1" :class="{ 'd-block': showModal }">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ isEditing ? 'Edit' : 'Tambah' }} Periode</h5>
            <button type="button" class="btn-close" @click="closeModal"></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="submitForm">
              <div class="mb-3">
                <label class="form-label">Tahun</label>
                <select v-model="formData.tahun_id" class="form-control" required>
                  <option value="">Pilih Tahun</option>
                  <option v-for="tahun in tahunList" :key="tahun.tahun_id" :value="tahun.tahun_id">
                    {{ tahun.nama }}
                  </option>
                </select>
              </div>

              <div class="mb-3">
                <label class="form-label">Lembaga Akreditasi</label>
                <select v-model="formData.la_id" class="form-control" required>
                  <option value="">Pilih Lembaga Akreditasi</option>
                  <option v-for="la in laList" :key="la.la_id" :value="la.la_id">
                    {{ la.nama }}
                  </option>
                </select>
              </div>

              <div class="mb-3">
                <label class="form-label">Periode Evaluasi Diri</label>
                <div class="d-flex gap-2">
                  <input type="date" v-model="formData.periode_edisi_start" class="form-control" required>
                  <span>-</span>
                  <input type="date" v-model="formData.periode_edisi_end" class="form-control" required>
                </div>
              </div>

              <div class="mb-3">
                <label class="form-label">Periode Desk Evaluation</label>
                <div class="d-flex gap-2">
                  <input type="date" v-model="formData.periode_edesk_start" class="form-control" required>
                  <span>-</span>
                  <input type="date" v-model="formData.periode_edesk_end" class="form-control" required>
                </div>
              </div>

              <div class="mb-3">
                <label class="form-label">Periode Visitasi</label>
                <div class="d-flex gap-2">
                  <input type="date" v-model="formData.periode_visitatif_start" class="form-control" required>
                  <span>-</span>
                  <input type="date" v-model="formData.periode_visitatif_end" class="form-control" required>
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
  </div>
</template>

<script>
import SearchBar from '../components/SearchBar.vue'

export default {
  name: 'Periode',
  components: { SearchBar },
  data() {
    return {
      periodeList: [],
      laList: [],
      tahunList: [],
      psmList: [],
      searchQuery: '',
      selectedTahun: '',
      selectedLA: '',
      showModal: false,
      isEditing: false,
      formData: {
        periode_id: '',
        lat_id: '',
        periode_edisi_start: '',
        periode_edisi_end: '',
        periode_edesk_start: '',
        periode_edesk_end: '',
        periode_visitatif_start: '',
        periode_visitatif_end: ''
      },
      selectedPSM: [],
      periodeOptions: []
    }
  },
  computed: {
    filteredList() {
      let filtered = this.periodeList

      // Filter berdasarkan tahun
      if (this.selectedTahun) {
        filtered = filtered.filter(item => 
          item.tahun_nama && item.tahun_nama === this.getTahunNama(this.selectedTahun)
        )
      }

      // Filter berdasarkan LA
      if (this.selectedLA) {
        filtered = filtered.filter(item => 
          item.la_nama && item.la_nama === this.getLANama(this.selectedLA)
        )
      }

      // Filter berdasarkan search query
      if (this.searchQuery) {
        const query = this.searchQuery.toLowerCase()
        filtered = filtered.filter(item => 
          (item.tahun_nama && item.tahun_nama.toLowerCase().includes(query)) ||
          (item.la_nama && item.la_nama.toLowerCase().includes(query)) ||
          (item.periode_edisi_start && item.periode_edisi_start.includes(query))
        )
      }

      return filtered
    }
  },
  methods: {
    getTahunNama(tahun_id) {
      if (!tahun_id) return ''
      console.log('tahun_id:', tahun_id, 'tahunList:', this.tahunList) // Debug
      const tahun = this.tahunList.find(t => t.tahun_id === tahun_id)
      return tahun ? tahun.nama : ''
    },
    getLANama(la_id) {
      if (!la_id) return ''
      console.log('la_id:', la_id, 'laList:', this.laList) // Debug
      const la = this.laList.find(l => l.la_id === la_id)
      return la ? la.nama : ''
    },
    formatDate(dateString) {
      if (!dateString) return ''
      const date = new Date(dateString)
      return date.toLocaleDateString('id-ID', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
      })
    },
    handleSearch(query) {
      this.searchQuery = query
    },
    async fetchData() {
      try {
        const response = await fetch('/api/periode.php?action=get_periode')
        const data = await response.json()
        console.log('Data periode detail:', data[0]) // Lihat struktur data periode
        this.periodeList = data
      } catch (error) {
        console.error('Error fetching periode:', error)
      }
    },
    async fetchTahun() {
      try {
        const response = await fetch('/api/periode.php?action=get_tahun')
        const data = await response.json()
        console.log('Data tahun:', data)
        this.tahunList = data
      } catch (error) {
        console.error('Error fetching tahun:', error)
      }
    },
    async fetchLA() {
      try {
        const response = await fetch('/api/periode.php?action=get_la')
        const data = await response.json()
        console.log('Data LA:', data)
        this.laList = data
      } catch (error) {
        console.error('Error fetching LA:', error)
      }
    },
    async fetchPSM() {
      try {
        const response = await fetch('/api/periode.php?action=get_psm')
        const data = await response.json()
        console.log('Data PSM:', data)
        this.psmList = data
      } catch (error) {
        console.error('Error fetching PSM:', error)
      }
    },
    async submitForm() {
      try {
        const url = this.isEditing 
          ? `/api/periode.php?action=update` 
          : '/api/periode.php?action=add'

        const response = await fetch(url, {
          method: this.isEditing ? 'PUT' : 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify(this.formData)
        })

        if (response.ok) {
          this.closeModal()
          this.fetchData()
          alert(this.isEditing ? 'Data berhasil diupdate!' : 'Data berhasil disimpan!')
        } else {
          const result = await response.json()
          alert('Gagal menyimpan data: ' + (result.error || 'Unknown error'))
        }
      } catch (error) {
        console.error('Error submitting data:', error)
        alert('Terjadi kesalahan saat menyimpan data')
      }
    },
    closeModal() {
      this.showModal = false
      this.isEditing = false
      this.selectedPSM = []
      this.formData = {
        periode_id: '',
        lat_id: '',
        periode_edisi_start: '',
        periode_edisi_end: '',
        periode_edesk_start: '',
        periode_edesk_end: '',
        periode_visitatif_start: '',
        periode_visitatif_end: ''
      }
    },
    updatePeriodeOptions() {
      this.periodeOptions = [...new Set(this.periodeList.map(item => item.nama_periode))]
    },
    handleEdit(item) {
      this.isEditing = true
      // Dapatkan tahun_id dan la_id dari data yang ada
      const tahun = this.tahunList.find(t => t.nama === item.tahun_nama)
      const la = this.laList.find(l => l.nama === item.la_nama)
      
      this.formData = {
        periode_id: item.periode_id,
        tahun_id: tahun ? tahun.tahun_id : '',
        la_id: la ? la.la_id : '',
        periode_edisi_start: item.periode_edisi_start,
        periode_edisi_end: item.periode_edisi_end,
        periode_edesk_start: item.periode_edesk_start,
        periode_edesk_end: item.periode_edesk_end,
        periode_visitatif_start: item.periode_visitatif_start,
        periode_visitatif_end: item.periode_visitatif_end
      }
      this.showModal = true
    },
    async handleDelete(periodeId) {
      if (confirm('Apakah Anda yakin ingin menghapus data ini?')) {
        try {
          const response = await fetch(`/api/periode.php?action=delete&id=${periodeId}`, {
            method: 'DELETE'
          })
          
          if (response.ok) {
            this.fetchData()
            alert('Data berhasil dihapus!')
          } else {
            const result = await response.json()
            alert('Gagal menghapus data: ' + (result.error || 'Unknown error'))
          }
        } catch (error) {
          console.error('Error deleting periode:', error)
          alert('Terjadi kesalahan saat menghapus data')
        }
      }
    }
  },
  mounted() {
    this.fetchData()
    this.fetchTahun()
    this.fetchLA()
    this.fetchPSM()
    this.updatePeriodeOptions()
  }
}
</script>

<style scoped>
.modal {
  background-color: rgba(0, 0, 0, 0.5);
}
.gap-3 {
  gap: 1rem;
}
.search-box {
  width: 300px;
}
.card-title {
  margin-bottom: 1rem;
}
.btn-sm {
  padding: 0.25rem 0.5rem;
  font-size: 0.875rem;
}
.me-2 {
  margin-right: 0.5rem;
}
</style> 