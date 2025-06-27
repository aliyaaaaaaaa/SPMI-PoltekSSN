<template>
  <div class="container mt-4">
    <h2>Nilai Mutu</h2>
    <!-- Filter Section -->
    <div class="card mb-4">
      <div class="card-body">
        <h5>Filter Data</h5>
        <div class="row">
          <div class="col-md-6">
            <div class="mb-3">
              <label class="form-label">Tahun</label>
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
              <select class="form-select" v-model="selectedLA">
                <option value="">Semua LA</option>
                <option v-for="la in laList" :key="la.la_id" :value="la.la_id">
                  {{ la.nama }}
                </option>
              </select>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Search dan Per Page dalam satu baris -->
    <div class="d-flex justify-content-between align-items-center mb-3">
      <div class="d-flex align-items-center gap-3">
        <input 
          type="text" 
          class="form-control" 
          placeholder="Cari..." 
          v-model="searchQuery"
          style="width: 250px;"
        >
        <div class="d-flex align-items-center">
          <span class="me-2">Per page:</span>
          <select v-model="perPage" class="form-select" style="width: auto;">
            <option value="10">10</option>
            <option value="25">25</option>
            <option value="50">50</option>
          </select>
        </div>
      </div>
      <button class="btn btn-primary" @click="showModal = true">
        Tambah
      </button>
    </div>

    <!-- Data Table -->
    <div class="table-responsive">
      <table class="table table-bordered">
        <thead>
          <tr>
            <th>No</th>
            <th>Nilai</th>
            <th>Keterangan</th>
            <th>Tahun</th>
            <th>Lembaga Akreditasi</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="filteredList.length === 0">
            <td colspan="6" class="text-center">
              Tidak ada data
            </td>
          </tr>
          
          <tr v-for="(item, index) in paginatedList" :key="item.nm_id">
            <td>{{ startIndex + index + 1 }}</td>
            <td>{{ item.nilai }}</td>
            <td>{{ item.keterangan }}</td>
            <td>{{ item.tahun_nama }}</td>
            <td>{{ item.la_nama }}</td>
            
            <td>
              <button class="btn btn-sm btn-primary me-2" @click="handleEdit(item)">Edit</button>
              <button class="btn btn-sm btn-danger" @click="handleDelete(item)">Hapus</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div class="d-flex justify-content-between align-items-center mt-3">
      <div>Showing {{ startIndex + 1 }} to {{ endIndex }} of {{ filteredList.length }} entries</div>
      <nav>
        <ul class="pagination mb-0">
          <li class="page-item" :class="{ disabled: currentPage === 1 }">
            <a class="page-link" href="#" @click.prevent="currentPage > 1 && currentPage--">Previous</a>
          </li>
          <li class="page-item active">
            <a class="page-link" href="#">{{ currentPage }}</a>
          </li>
          <li class="page-item" :class="{ disabled: currentPage === totalPages }">
            <a class="page-link" href="#" @click.prevent="currentPage < totalPages && currentPage++">Next</a>
          </li>
        </ul>
      </nav>
    </div>

    <!-- Modal Form -->
    <div class="modal" tabindex="-1" :class="{ 'd-block': showModal }">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ isEditing ? 'Edit' : 'Tambah' }} Nilai Mutu</h5>
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
                  <option value="">Pilih Lembaga</option>
                  <option v-for="la in laList" :key="la.la_id" :value="la.la_id">
                    {{ la.nama }}
                  </option>
                </select>
              </div>
              <div class="mb-3">
                <label class="form-label">Nilai</label>
                <input type="number" v-model="formData.nilai" class="form-control" required min="0" max="100">
              </div>
              <div class="mb-3">
                <label class="form-label">Keterangan</label>
                <input type="text" v-model="formData.keterangan" class="form-control" required>
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
  components: { SearchBar },
  data() {
    return {
      nilaiList: [],
      tahunList: [],
      laList: [],
      selectedTahun: '',
      selectedLA: '',
      searchQuery: '',
      showModal: false,
      isEditing: false,
      formData: {
        nm_id: '',
        nilai: '',
        keterangan: '',
        tahun_id: '',
        la_id: ''
      },
      currentPage: 1,
      perPage: 10
    }
  },
  computed: {
    filteredList() {
      let filtered = this.nilaiList

      // Filter berdasarkan tahun yang dipilih
      if (this.selectedTahun) {
        filtered = filtered.filter(item => item.tahun_id === this.selectedTahun)
      }

      // Filter berdasarkan lembaga akreditasi yang dipilih
      if (this.selectedLA) {
        filtered = filtered.filter(item => item.la_id === this.selectedLA)
      }

      // Filter berdasarkan pencarian
      if (this.searchQuery) {
        const query = this.searchQuery.toLowerCase()
        filtered = filtered.filter(item => 
          item.nilai.toString().includes(query) ||
          (item.keterangan && item.keterangan.toLowerCase().includes(query))
        )
      }

      return filtered
    },
    paginatedList() {
      const start = (this.currentPage - 1) * this.perPage
      const end = start + this.perPage
      return this.filteredList.slice(start, end)
    },
    totalPages() {
      return Math.ceil(this.filteredList.length / this.perPage)
    },
    startIndex() {
      return (this.currentPage - 1) * this.perPage
    },
    endIndex() {
      return Math.min(this.startIndex + this.perPage, this.filteredList.length)
    }
  },
  methods: {
    handleSearch(query) {
      this.searchQuery = query
    },
    async fetchNilaiMutu() {
      try {
        const response = await fetch('/api/nilai_mutu.php?action=get_nilai')
        const data = await response.json()
        console.log('Data nilai mutu:', data)
        this.nilaiList = data
      } catch (error) {
        console.error('Error fetching nilai:', error)
      }
    },
    async fetchTahun() {
      try {
        const response = await fetch('/api/nilai_mutu.php?action=get_tahun')
        const data = await response.json()
        console.log('Data tahun:', data)
        this.tahunList = data
      } catch (error) {
        console.error('Error fetching tahun:', error)
      }
    },
    async fetchLA() {
      try {
        const response = await fetch('/api/nilai_mutu.php?action=get_la')
        const data = await response.json()
        console.log('Data LA:', data)
        this.laList = data
      } catch (error) {
        console.error('Error fetching LA:', error)
      }
    },
    async fetchLAT() {
      try {
        const response = await fetch('/api/nilai_mutu.php?action=get_lat')
        const data = await response.json()
        this.latList = data
      } catch (error) {
        console.error('Error fetching LAT:', error)
      }
    },
    handleEdit(item) {
      this.isEditing = true
      this.formData = {
        nm_id: item.nm_id,
        tahun_id: item.tahun_id,
        la_id: item.la_id,
        nilai: item.nilai,
        keterangan: item.keterangan
      }
      this.showModal = true
    },
    async handleDelete(item) {
      if (confirm('Apakah Anda yakin ingin menghapus data ini?')) {
        try {
          const response = await fetch('/api/nilai_mutu.php?action=delete', {
            method: 'DELETE',
            headers: {
              'Content-Type': 'application/json'
            },
            body: JSON.stringify({ nm_id: item.nm_id })
          })
          if (response.ok) {
            await this.fetchNilaiMutu()
            alert('Data berhasil dihapus!')
          } else {
            const error = await response.json()
            alert('Gagal menghapus data: ' + (error.message || error.error || ''))
          }
        } catch (error) {
          console.error('Error deleting data:', error)
          alert('Terjadi kesalahan saat menghapus data')
        }
      }
    },
    closeModal() {
      this.showModal = false
      this.isEditing = false
      this.formData = {
        nm_id: '',
        tahun_id: '',
        la_id: '',
        nilai: '',
        keterangan: ''
      }
    },
    async submitForm() {
      try {
        // Cari lat_id berdasarkan tahun_id dan la_id
        const lat = this.latList.find(
          l => l.tahun_id == this.formData.tahun_id && l.la_id == this.formData.la_id
        )
        if (!lat) {
          alert('Kombinasi Tahun dan Lembaga Akreditasi tidak ditemukan di LAT!')
          return
        }
        const payload = {
          lat_id: lat.lat_id,
          nilai: this.formData.nilai,
          keterangan: this.formData.keterangan
        }
        if (this.isEditing) payload.nm_id = this.formData.nm_id

        const url = this.isEditing 
          ? '/api/nilai_mutu.php?action=update' 
          : '/api/nilai_mutu.php?action=add'

        const response = await fetch(url, {
          method: this.isEditing ? 'PUT' : 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify(payload)
        })

        let result;
        const contentType = response.headers.get('content-type');
        if (contentType && contentType.includes('application/json')) {
          result = await response.json();
        } else {
          const text = await response.text();
          throw new Error(`Server returned non-JSON response: ${text}`);
        }

        if (response.ok) {
          this.closeModal()
          this.fetchNilaiMutu()
          alert(this.isEditing ? 'Data berhasil diupdate!' : 'Data berhasil disimpan!')
        } else {
          alert('Gagal menyimpan data: ' + (result.message || result.error || 'Unknown error'))
        }
      } catch (error) {
        console.error('Error submitting form:', error)
        alert('Terjadi kesalahan saat menyimpan data: ' + error.message)
      }
    }
  },
  mounted() {
    this.fetchNilaiMutu()
    this.fetchTahun()
    this.fetchLA()
    this.fetchLAT()
  }
}
</script>

<style scoped>
.modal {
  background-color: rgba(0, 0, 0, 0.5);
}
</style> 