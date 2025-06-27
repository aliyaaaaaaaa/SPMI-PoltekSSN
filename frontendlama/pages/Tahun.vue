<template>
  <div class="container mt-4">
    <h2>Tahun</h2>

    <!-- Filter Box -->
    <div class="card mb-3">
      <div class="card-body">
        <h5 class="card-title">Filter Data</h5>
        <div>
          <div>Status</div>
          <select v-model="selectedStatus" class="form-control">
            <option value="">Semua Status</option>
            <option value="1">Aktif</option>
            <option value="2">Tidak Aktif</option>
          </select>
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
            placeholder="Cari tahun..." 
            v-model="searchQuery"
          >
        </div>
        <!-- Per Page -->
        <div class="d-flex align-items-center">
          <span class="me-2">Per page:</span>
          <select v-model="perPage" class="form-select" style="width: auto;">
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
            <th>Tahun</th>
            <th>Status</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(item, index) in paginatedList" :key="item.tahun_id">
            <td>{{ startIndex + index + 1 }}</td>
            <td>{{ item.nama }}</td>
            <td>{{ item.status_id === 1 ? 'Aktif' : 'Tidak Aktif' }}</td>
            <td>
              <button class="btn btn-primary btn-sm me-2" @click="handleEdit(item)">Edit</button>
              <button 
                class="btn btn-danger btn-sm" 
                @click="handleDelete(item.tahun_id)"
                :disabled="item.status_id === 1"
              >
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
            <a class="page-link text-primary border-0" href="#" @click.prevent="currentPage > 1 && currentPage--">Previous</a>
          </li>
          <li class="page-item active">
            <a class="page-link" href="#">{{ currentPage }}</a>
          </li>
          <li class="page-item" :class="{ disabled: currentPage === totalPages }">
            <a class="page-link text-success border-0" href="#" @click.prevent="currentPage < totalPages && currentPage++">Next</a>
          </li>
        </ul>
      </nav>
    </div>

    <!-- Modal -->
    <div v-if="showModal" class="modal d-block">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ isEditing ? 'Edit Tahun' : 'Tambah Tahun' }}</h5>
            <button type="button" class="btn-close" @click="showModal = false"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Tahun</label>
              <input type="number" class="form-control" v-model="formData.nama">
            </div>
            <div class="mb-3">
              <label class="form-label">Status</label>
              <select class="form-select" v-model="formData.status_id">
                <option :value="2">Tidak Aktif</option>
                <option :value="1">Aktif</option>
              </select>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="showModal = false">Batal</button>
            <button type="button" class="btn btn-primary" @click="handleSubmit">Simpan</button>
          </div>
        </div>
      </div>
    </div>
    <div v-if="showModal" class="modal-backdrop fade show"></div>
  </div>
</template>

<script>
export default {
  name: 'Tahun',
  data() {
    return {
      currentPage: 1,
      perPage: 10,
      tahunList: [],
      selectedStatus: '',
      searchQuery: '',
      showModal: false,
      isEditing: false,
      formData: {
        tahun_id: '',
        nama: '',
        status_id: 2
      }
    }
  },
  computed: {
    filteredList() {
      let filtered = this.tahunList

      if (this.selectedStatus !== '') {
        filtered = filtered.filter(item => 
          item.status_id.toString() === this.selectedStatus
        )
      }

      if (this.searchQuery) {
        const query = this.searchQuery.toLowerCase()
        filtered = filtered.filter(item => 
          item.nama.toLowerCase().includes(query) ||
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
    },
  },
  mounted() {
    this.fetchData()
  },
  methods: {
    async fetchData() {
      try {
        const response = await fetch('/api/tahun.php?action=get_tahun')
        const data = await response.json()
        console.log('Data tahun:', data)
        this.tahunList = data
      } catch (error) {
        console.error('Error fetching tahun:', error)
      }
    },
    handleAdd() {
      this.isEditing = false
      this.formData = {
        tahun_id: '',
        nama: '',
        status_id: 2
      }
      this.showModal = true
    },
    handleEdit(item) {
      this.isEditing = true
      this.formData = { ...item }
      this.showModal = true
    },
    async handleSubmit() {
      try {
        const url = this.isEditing 
          ? `/api/tahun.php?action=update` 
          : '/api/tahun.php?action=add'

        const response = await fetch(url, {
          method: this.isEditing ? 'PUT' : 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify(this.formData)
        })

        if (response.ok) {
          this.showModal = false
          this.fetchData()
          alert(this.isEditing ? 'Data berhasil diupdate!' : 'Data berhasil disimpan!')
        } else {
          alert('Gagal menyimpan data')
        }
      } catch (error) {
        console.error('Error submitting data:', error)
      }
    },
    async handleDelete(id) {
      if (confirm('Apakah Anda yakin ingin menghapus data ini?')) {
        try {
          const response = await fetch(`/api/tahun.php?action=delete&id=${id}`, {
            method: 'DELETE'
          })
          if (response.ok) {
            this.fetchData()
            alert('Data berhasil dihapus!')
          } else {
            alert('Gagal menghapus data')
          }
        } catch (error) {
          console.error('Error deleting data:', error)
        }
      }
    }
  }
}
</script>

<style scoped>
.search-box {
  width: 300px;
}
.gap-3 {
  gap: 1rem;
}
.card-title {
  margin-bottom: 1rem;
}
.form-control {
  width: 100%;
}
.modal {
  background-color: rgba(0,0,0,0.5);
}
</style> 