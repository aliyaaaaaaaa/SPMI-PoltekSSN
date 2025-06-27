<template>
  <div class="container mt-4">
    <h2>Kategori Dokumen</h2>

    <!-- Search, Per page, dan Tambah -->
    <div class="d-flex justify-content-between align-items-center mb-3">
      <div class="d-flex align-items-center gap-3">
        <!-- Search Bar -->
        <div class="search-box">
          <input 
            type="text" 
            class="form-control" 
            placeholder="Cari kategori..." 
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
      <button class="btn btn-primary" @click="showAddModal = true">
        Tambah
      </button>
    </div>

    <!-- Table -->
    <div class="table-responsive">
      <table class="table">
        <thead>
          <tr>
            <th>No</th>
            <th>Kategori Dokumen</th>
            <th>Keterangan</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(item, index) in paginatedList" :key="item.kd_id">
            <td>{{ startIndex + index + 1 }}</td>
            <td>{{ item.nama_kd }}</td>
            <td>{{ item.keterangan || '-' }}</td>
            <td>
              <button class="btn btn-primary btn-sm me-2" @click="editItem(item)">Edit</button>
              <button class="btn btn-danger btn-sm" @click="deleteItem(item.kd_id)">Hapus</button>
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
    <div class="modal" tabindex="-1" :class="{ 'd-block': showAddModal }">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ isEditing ? 'Edit' : 'Tambah' }} Kategori Dokumen</h5>
            <button type="button" class="btn-close" @click="closeModal"></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="submitForm">
              <div class="mb-3">
                <label class="form-label">Kategori Dokumen</label>
                <input type="text" v-model="formData.nama_kd" class="form-control" required>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" @click="closeModal">Tutup</button>
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
      kategoriList: [],
      kategoriOptions: [],
      selectedKategori: '',
      searchQuery: '',
      showAddModal: false,
      isEditing: false,
      formData: {
        kd_id: '',
        nama_kd: '',
        keterangan: ''
      },
      currentPage: 1,
      perPage: 10
    }
  },
  computed: {
    filteredList() {
      let filtered = this.kategoriList
      if (this.searchQuery) {
        const query = this.searchQuery.toLowerCase()
        filtered = filtered.filter(item => 
          item.nama_kd.toLowerCase().includes(query) ||
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
  mounted() {
    this.fetchData()
    this.updateKategoriOptions()
  },
  methods: {
    updateKategoriOptions() {
      this.kategoriOptions = [...new Set(this.kategoriList.map(item => item.nama_kd))]
    },
    async fetchData() {
      try {
        const response = await fetch('/api/kategori_dokumen.php?action=get_kategori')
        const data = await response.json()
        console.log('Data Kategori:', data)
        this.kategoriList = data
      } catch (error) {
        console.error('Error fetching Kategori:', error)
      }
    },
    editItem(item) {
      this.isEditing = true
      this.formData = { ...item }
      this.showAddModal = true
    },
    async deleteItem(kdId) {
      if (confirm('Apakah Anda yakin ingin menghapus data ini?')) {
        try {
          const response = await fetch(`/api/kategori_dokumen.php?action=delete&id=${kdId}`, {
            method: 'DELETE'
          })
          const result = await response.json()
          if (response.ok) {
            this.fetchData()
            alert('Data berhasil dihapus!')
          } else {
            alert('Gagal menghapus data: ' + result.error)
          }
        } catch (error) {
          console.error('Error deleting Kategori:', error)
          alert('Terjadi kesalahan saat menghapus data')
        }
      }
    },
    closeModal() {
      this.showAddModal = false
      this.isEditing = false
      this.formData = {
        kd_id: '',
        nama_kd: '',
        keterangan: ''
      }
    },
    async submitForm() {
      try {
        const url = this.isEditing 
          ? '/api/kategori_dokumen.php?action=update' 
          : '/api/kategori_dokumen.php?action=add'

        const response = await fetch(url, {
          method: this.isEditing ? 'PUT' : 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify(this.formData)
        })

        const result = await response.json()
        if (response.ok) {
          this.closeModal()
          this.fetchData()
          alert(this.isEditing ? 'Data berhasil diupdate!' : 'Data berhasil disimpan!')
        } else {
          alert('Gagal menyimpan data: ' + result.error)
        }
      } catch (error) {
        console.error('Error submitting form:', error)
        alert('Terjadi kesalahan saat menyimpan data')
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
.modal {
  background-color: rgba(0, 0, 0, 0.5);
}
</style> 