<template>
  <div class="container mt-4">
    <h2>Kategori Temuan</h2>
    <!-- Search, Per page, dan Tambah -->
    <div class="d-flex justify-content-between align-items-center mb-3">
      <div class="d-flex align-items-center gap-3">
        <!-- Search Bar -->
        <div class="search-box">
          <input 
            type="text" 
            class="form-control" 
            placeholder="Cari kategori temuan..." 
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
            <th>Kategori Temuan</th>
            <th>Keterangan</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(item, index) in paginatedList" :key="item.kt_id">
            <td>{{ startIndex + index + 1 }}</td>
            <td>{{ item.nama_kt }}</td>
            <td>{{ item.keterangan || '-' }}</td>
            <td>
              <button class="btn btn-primary btn-sm me-2" @click="editItem(item)">Edit</button>
              <button class="btn btn-danger btn-sm" @click="deleteItem(item.kt_id)">Hapus</button>
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
            <h5 class="modal-title">{{ isEditing ? 'Edit' : 'Tambah' }} Kategori Temuan</h5>
            <button type="button" class="btn-close" @click="closeModal"></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="submitForm">
              <div class="mb-3">
                <label class="form-label">Nama Kategori</label>
                <input type="text" v-model="formData.nama_kt" class="form-control" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Keterangan</label>
                <textarea v-model="formData.keterangan" class="form-control" rows="3"></textarea>
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
  name: 'KategoriTemuan',
  data() {
    return {
      ktList: [],
      kategoriOptions: [],
      selectedKategori: '',
      searchQuery: '',
      showAddModal: false,
      isEditing: false,
      formData: {
        kt_id: '',
        nama_kt: '',
        keterangan: ''
      },
      showKategoriDropdown: false,
      currentPage: 1,
      perPage: 10,
      kategoriList: []
    }
  },
  computed: {
    filteredList() {
      let filtered = this.kategoriList
      if (this.searchQuery) {
        const query = this.searchQuery.toLowerCase()
        filtered = filtered.filter(item => 
          (item.nama_kt && item.nama_kt.toLowerCase().includes(query)) ||
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
      this.kategoriOptions = [...new Set(this.ktList.map(item => item.nama_kt))]
    },
    async fetchData() {
      try {
        const response = await fetch('/api/kt.php?action=get_kt')
        const data = await response.json()
        console.log('Data kategori temuan:', data)
        this.ktList = data
        this.kategoriList = data
      } catch (error) {
        console.error('Error fetching kategori temuan:', error)
      }
    },
    editItem(item) {
      this.isEditing = true
      this.formData = { ...item }
      this.showAddModal = true
    },
    async deleteItem(ktId) {
      if (confirm('Apakah Anda yakin ingin menghapus data ini?')) {
        try {
          const response = await fetch(`/api/kt.php?action=delete&id=${ktId}`, {
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
          console.error('Error deleting KT:', error)
          alert('Terjadi kesalahan saat menghapus data')
        }
      }
    },
    closeModal() {
      this.showAddModal = false
      this.isEditing = false
      this.formData = {
        kt_id: '',
        nama_kt: '',
        keterangan: ''
      }
    },
    async submitForm() {
      try {
        const url = this.isEditing 
          ? '/api/kt.php?action=update' 
          : '/api/kt.php?action=add'

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
.form-label {
  display: block;
  margin-bottom: 0.5rem;
}
.modal {
  background-color: rgba(0, 0, 0, 0.5);
}
.form-control {
  width: 100%;
}
</style> 