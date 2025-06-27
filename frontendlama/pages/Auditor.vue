<template>
  <div class="container mt-4">
    <h2>Auditor</h2>
    <!-- Search dan Per Page -->
    <div class="d-flex justify-content-between align-items-center mb-3">
      <div class="d-flex align-items-center gap-3">
        <input 
          type="text" 
          class="form-control" 
          placeholder="Cari auditor..." 
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
            <th>NIK</th>
            <th>Nama</th>
            <th>Jabatan</th>
            <th>Instansi</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(item, index) in paginatedList" :key="item.role_id">
            <td>{{ startIndex + index + 1 }}</td>
            <td>{{ item.role_id }}</td>
            <td>{{ item.nama }}</td>
            <td>{{ item.jabatan }}</td>
            <td>{{ item.instansi }}</td>
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
            <h5 class="modal-title">{{ isEditing ? 'Edit' : 'Tambah' }} Auditor</h5>
            <button type="button" class="btn-close" @click="closeModal"></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="submitForm">
              <div class="mb-3">
                <label class="form-label">NIK</label>
                <input 
                  type="text" 
                  class="form-control" 
                  v-model="formData.role_id"
                  :disabled="isEditing"
                  required
                >
              </div>

              <div class="mb-3">
                <label class="form-label">Nama</label>
                <input type="text" class="form-control" v-model="formData.nama" required>
              </div>

              <div class="mb-3">
                <label class="form-label">Jabatan</label>
                <input type="text" class="form-control" v-model="formData.jabatan" required>
              </div>

              <div class="mb-3">
                <label class="form-label">Instansi</label>
                <input type="text" class="form-control" v-model="formData.instansi" required>
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
  data() {
    return {
      auditorList: [],
      searchQuery: '',
      currentPage: 1,
      perPage: 10,
      showModal: false,
      isEditing: false,
      formData: {
        role_id: '',
        nama: '',
        jabatan: '',
        instansi: ''
      }
    }
  },
  computed: {
    filteredList() {
      let filtered = this.auditorList
      if (this.searchQuery) {
        const query = this.searchQuery.toLowerCase()
        filtered = filtered.filter(item => 
          item.role_id.toString().includes(query) ||
          item.nama.toLowerCase().includes(query) ||
          item.jabatan.toLowerCase().includes(query) ||
          item.instansi.toLowerCase().includes(query)
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
    async fetchData() {
      try {
        const response = await fetch('/api/auditor.php?action=get_auditor')
        this.auditorList = await response.json()
      } catch (error) {
        console.error('Error fetching auditor:', error)
      }
    },
    handleEdit(item) {
      this.isEditing = true
      this.formData = { ...item }
      this.showModal = true
    },
    async handleDelete(id) {
      if (confirm('Apakah Anda yakin ingin menghapus data ini?')) {
        try {
          const response = await fetch(`/api/auditor.php?action=delete&id=${id}`, {
            method: 'DELETE'
          })
          if (response.ok) {
            this.fetchData()
            alert('Data berhasil dihapus!')
          } else {
            alert('Gagal menghapus data')
          }
        } catch (error) {
          console.error('Error deleting auditor:', error)
        }
      }
    },
    closeModal() {
      this.showModal = false
      this.isEditing = false
      this.formData = {
        role_id: '',
        nama: '',
        jabatan: '',
        instansi: ''
      }
    },
    async submitForm() {
      try {
        const url = this.isEditing 
          ? `/api/auditor.php?action=update` 
          : '/api/auditor.php?action=add'

        const response = await fetch(url, {
          method: 'POST',
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
</style> 