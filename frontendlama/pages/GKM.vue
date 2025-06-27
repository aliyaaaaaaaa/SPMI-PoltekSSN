<template>
  <div class="container mt-4">
    <h2>Gugus Kendali Mutu</h2>
    <!-- Search dan Per Page -->
    <div class="d-flex justify-content-between align-items-center mb-3">
      <div class="d-flex align-items-center gap-3">
        <input 
          type="text" 
          class="form-control" 
          placeholder="Cari kode/nama GKM..." 
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
            <th>Nama GKM</th>
            <th>Keterangan</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(item, index) in paginatedList" :key="item.role_id">
            <td>{{ startIndex + index + 1 }}</td>
            <td>{{ item.role_id }}</td>
            <td>{{ item.nama }}</td>
            <td>{{ item.keterangan }}</td>
            <td>
              <button class="btn btn-sm btn-warning me-2" @click="handleEdit(item)">
                Edit
              </button>
              <button class="btn btn-sm btn-danger" @click="handleDelete(item.role_id)">
                Hapus
              </button>
            </td>
          </tr>
          <tr v-if="filteredList.length === 0">
            <td colspan="5" class="text-center">No data available in table</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div class="d-flex justify-content-between align-items-center mt-3">
      <div>Showing {{ startIndex + 1 }} to {{ endIndex }} of {{ filteredList.length }} entries</div>
      <nav>
        <ul class="pagination mb-0">
          <li class="page-item">
            <a class="page-link" href="#" @click.prevent="currentPage > 1 && currentPage--">Previous</a>
          </li>
          <li class="page-item active">
            <a class="page-link" href="#">{{ currentPage }}</a>
          </li>
          <li class="page-item">
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
            <h5 class="modal-title">{{ isEditing ? 'Edit' : 'Tambah' }} GKM</h5>
            <button type="button" class="btn-close" @click="closeModal"></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="submitForm">
              <div class="mb-3">
                <label class="form-label">Nama GKM</label>
                <input type="text" class="form-control" v-model="formData.nama" required>
              </div>

              <div class="mb-3">
                <label class="form-label">Keterangan</label>
                <textarea class="form-control" v-model="formData.keterangan" rows="3"></textarea>
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
  name: 'GKM',
  data() {
    return {
      gkmList: [],
      searchQuery: '',
      currentPage: 1,
      perPage: 10,
      showModal: false,
      isEditing: false,
      formData: {
        role_id: '',
        nama: '',
        keterangan: '',
        grup_id: 4 // Default grup_id untuk GKM
      }
    }
  },
  computed: {
    filteredList() {
      let filtered = this.gkmList

      if (this.searchQuery) {
        const query = this.searchQuery.toLowerCase()
        filtered = filtered.filter(item => 
          item.role_id.toString().includes(query) ||
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
      const end = this.startIndex + this.perPage
      return Math.min(end, this.filteredList.length)
    }
  },
  methods: {
    async fetchData() {
      try {
        const response = await fetch('/api/gkm.php?action=get_gkm')
        const data = await response.json()
        this.gkmList = data
      } catch (error) {
        console.error('Error fetching GKM:', error)
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
          const response = await fetch(`/api/gkm.php?action=delete&id=${roleId}`, {
            method: 'DELETE'
          })
          if (response.ok) {
            this.fetchData()
            alert('Data berhasil dihapus!')
          } else {
            alert('Gagal menghapus data')
          }
        } catch (error) {
          console.error('Error deleting GKM:', error)
        }
      }
    },
    closeModal() {
      this.showModal = false
      this.isEditing = false
      this.formData = {
        role_id: '',
        nama: '',
        keterangan: '',
        grup_id: 4
      }
    },
    async submitForm() {
      try {
        const url = this.isEditing 
          ? `/api/gkm.php?action=update` 
          : '/api/gkm.php?action=add'

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