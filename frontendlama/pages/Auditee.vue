<template>
  <div class="container mt-4">
    <h2>Pengaturan Program Studi</h2>

    <!-- Filter Data -->
    <div class="card mb-4">
      <div class="card-body">
        <h5>Filter Data</h5>
        <div class="row">
          <div class="col-md-6">
            <div class="mb-3">
              <label class="form-label">Program Studi</label>
              <select v-model="selectedProdi" class="form-control">
                <option value="">Semua Program Studi</option>
                <option v-for="prodi in prodiList" :key="prodi.role_id" :value="prodi.role_id">
                  {{ prodi.nama }}
                </option>
              </select>
            </div>
          </div>
          <div class="col-md-6">
            <div class="mb-3">
              <label class="form-label">Akreditasi</label>
              <select v-model="selectedAkreditasi" class="form-control">
                <option value="">Semua Akreditasi</option>
                <option v-for="akr in sortedAkreditasiList" :key="akr.akreditasi_id" :value="akr.akreditasi_id">
                  {{ akr.nama }}
                </option>
              </select>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Search dan Per Page -->
    <div class="d-flex justify-content-between align-items-center mb-3">
      <div class="d-flex align-items-center gap-3">
        <input 
          type="text" 
          class="form-control" 
          placeholder="Cari prodi..." 
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

    <!-- Table -->
    <div class="table-responsive">
      <table class="table">
        <thead>
          <tr>
            <th>No</th>
            <th>Kode</th>
            <th>Program Studi</th>
            <th>Akreditasi</th>
            <th>Nomor SK Akreditasi</th>
            <th>Tanggal Akreditasi</th>
            <th>Tanggal Expired</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(item, index) in paginatedList" :key="item.role_id">
            <td>{{ index + 1 }}</td>
            <td>{{ item.role_id }}</td>
            <td>{{ item.nama }}</td>
            <td>{{ getAkreditasiNama(item.akreditasi_id) }}</td>
            <td>{{ item.nomorSK }}</td>
            <td>{{ formatDate(item.tanggal_akreditasi) }}</td>
            <td>{{ formatDate(item.tanggal_expired) }}</td>
            <td>
              <button class="btn btn-sm btn-warning me-2" @click="handleEdit(item)">
                Edit
              </button>
              <button class="btn btn-sm btn-danger" @click="handleDelete(item.role_id)">
                Hapus
              </button>
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
            <h5 class="modal-title">{{ isEditing ? 'Edit' : 'Tambah' }} Program Studi</h5>
            <button type="button" class="btn-close" @click="closeModal"></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="submitForm">
              <div class="mb-3">
                <label class="form-label">Nama Program Studi</label>
                <input type="text" class="form-control" v-model="formData.nama" required>
              </div>

              <div class="mb-3">
                <label class="form-label">Akreditasi</label>
                <select v-model="formData.akreditasi_id" class="form-control" required>
                  <option value="">-- PILIH --</option>
                  <option v-for="akr in sortedAkreditasiList" :key="akr.akreditasi_id" :value="akr.akreditasi_id">
                    {{ akr.nama }}
                  </option>
                </select>
              </div>

              <div class="mb-3">
                <label class="form-label">No SK Akreditasi</label>
                <input type="text" class="form-control" v-model="formData.nomorSK" required>
              </div>

              <div class="mb-3">
                <label class="form-label">Tanggal Akreditasi</label>
                <input type="date" class="form-control" v-model="formData.tanggal_akreditasi" required>
              </div>

              <div class="mb-3">
                <label class="form-label">Tanggal Expired</label>
                <input type="date" class="form-control" v-model="formData.tanggal_expired" required>
              </div>

              <div class="mb-3">
                <label class="form-label">Dokumen Akreditasi</label>
                <input 
                  type="file" 
                  class="form-control" 
                  @change="handleFileUpload" 
                  accept=".pdf"
                >
                <small class="text-muted">*) Format file harus berupa PDF dan ukuran file tidak boleh melebihi 2MB.</small>
                <div v-if="isEditing && formData.dokumenakreditasi" class="mt-2">
                  <small>File saat ini: {{ formData.dokumenakreditasi }}</small>
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
export default {
  name: 'Auditee',
  data() {
    return {
      currentPage: 1,
      perPage: 10,
      prodiList: [],
      akreditasiList: [],
      searchQuery: '',
      showModal: false,
      isEditing: false,
      formData: {
        role_id: '',
        nama: '',
        akreditasi_id: '',
        nomorSK: '',
        tanggal_akreditasi: '',
        tanggal_expired: '',
        standar_id: 1,
        grup_id: 3,
        dokumenakreditasi: null
      },
      selectedProdi: '',
      selectedAkreditasi: '',
      selectedFile: null
    }
  },
  computed: {
    filteredList() {
      let filtered = this.prodiList

      // Filter by Prodi
      if (this.selectedProdi) {
        filtered = filtered.filter(item => 
          item.role_id.toString() === this.selectedProdi.toString()
        )
      }

      // Filter by Akreditasi
      if (this.selectedAkreditasi) {
        filtered = filtered.filter(item => 
          item.akreditasi_id.toString() === this.selectedAkreditasi.toString()
        )
      }

      // Filter by search query
      if (this.searchQuery) {
        const query = this.searchQuery.toLowerCase()
        filtered = filtered.filter(item => 
          item.nama.toLowerCase().includes(query) ||
          this.getAkreditasiNama(item.akreditasi_id).toLowerCase().includes(query) ||
          item.nomorSK.toLowerCase().includes(query)
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
    },
    sortedAkreditasiList() {
      // Urutan yang diinginkan
      const order = ['A', 'B', 'C', 'Baik', 'Baik Sekali', 'Unggul'];
      
      return [...this.akreditasiList].sort((a, b) => {
        return order.indexOf(a.nama) - order.indexOf(b.nama);
      });
    }
  },
  watch: {
    // Reset page when filters change
    selectedProdi() {
      this.currentPage = 1
    },
    selectedAkreditasi() {
      this.currentPage = 1
    },
    searchQuery() {
      this.currentPage = 1
    },
    perPage() {
      this.currentPage = 1
    }
  },
  methods: {
    formatDate(date) {
      if (!date) return ''
      return new Date(date).toLocaleDateString('id-ID')
    },
    getAkreditasiNama(id) {
      const akr = this.akreditasiList.find(a => a.akreditasi_id === id)
      return akr ? akr.nama : ''
    },
    async fetchData() {
      try {
        const [prodiResponse, akreditasiResponse] = await Promise.all([
          fetch('/api/prodi.php?action=get_prodi'),
          fetch('/api/prodi.php?action=get_akreditasi')
        ])
        
        const prodiData = await prodiResponse.json()
        const akreditasiData = await akreditasiResponse.json()
        
        this.prodiList = prodiData
        this.akreditasiList = akreditasiData
      } catch (error) {
        console.error('Error fetching data:', error)
      }
    },
    handleEdit(item) {
      this.isEditing = true
      this.formData = { ...item }
      this.showModal = true
    },
    async handleDelete(roleId) {
      if (confirm('Apakah Anda yakin ingin menghapus data ini?')) {
        try {
          const response = await fetch(`/api/prodi.php?action=delete&id=${roleId}`, {
            method: 'DELETE'
          })
          if (response.ok) {
            this.fetchData()
            alert('Data berhasil dihapus!')
          } else {
            alert('Gagal menghapus data')
          }
        } catch (error) {
          console.error('Error deleting prodi:', error)
        }
      }
    },
    closeModal() {
      this.showModal = false
      this.isEditing = false
      this.selectedFile = null
      this.formData = {
        role_id: '',
        nama: '',
        akreditasi_id: '',
        nomorSK: '',
        tanggal_akreditasi: '',
        tanggal_expired: '',
        standar_id: 1,
        grup_id: 3,
        dokumenakreditasi: null
      }
    },
    handleFileUpload(event) {
      const file = event.target.files[0]
      if (file) {
        if (file.size > 2 * 1024 * 1024) {
          alert('Ukuran file tidak boleh melebihi 2MB')
          event.target.value = ''
          return
        }
        if (file.type !== 'application/pdf') {
          alert('Format file harus PDF')
          event.target.value = ''
          return
        }
        this.selectedFile = file
      }
    },
    async submitForm() {
      try {
        const formData = new FormData()
        
        // Append semua field formData
        for (const key in this.formData) {
          if (key !== 'dokumenakreditasi') { // Skip file field
            formData.append(key, this.formData[key])
          }
        }

        // Append file jika ada
        if (this.selectedFile) {
          formData.append('dokumenakreditasi', this.selectedFile)
        }

        const url = this.isEditing 
          ? `/api/prodi.php?action=update` 
          : '/api/prodi.php?action=add'

        const response = await fetch(url, {
          method: 'POST',
          body: formData
        })

        if (response.ok) {
          this.closeModal()
          this.fetchData()
          alert(this.isEditing ? 'Data berhasil diupdate!' : 'Data berhasil disimpan!')
        } else {
          alert('Gagal menyimpan data')
        }
      } catch (error) {
        console.error('Error submitting data:', error)
      }
    }
  },
  mounted() {
    this.fetchData()
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
.card {
  margin-bottom: 1rem;
}
.btn:disabled {
  cursor: not-allowed;
  opacity: 0.6;
}
</style> 