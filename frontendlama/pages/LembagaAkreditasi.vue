<template>
  <div class="container mt-4">
    <h2>Lembaga Akreditasi</h2>
    
    <!-- Search, Per page, dan Tambah -->
    <div class="d-flex justify-content-between align-items-center mb-3">
      <div class="d-flex align-items-center gap-3">
        <!-- Search Bar -->
        <div class="search-box">
          <input 
            type="text" 
            class="form-control" 
            placeholder="Cari lembaga..." 
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
            <th>Nama Lembaga</th>
            <th>Keterangan</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(item, index) in paginatedList" :key="item.la_id">
            <td>{{ startIndex + index + 1 }}</td>
            <td>{{ item.nama }}</td>
            <td>{{ item.keterangan || '-' }}</td>
            <td>
              <button class="btn btn-primary btn-sm me-2" @click="handleEdit(item)">Edit</button>
              <button class="btn btn-danger btn-sm" @click="handleDelete(item.la_id)">Hapus</button>
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
            <h5 class="modal-title">{{ isEditing ? 'Edit' : 'Tambah' }} Lembaga Akreditasi</h5>
            <button type="button" class="btn-close" @click="showModal = false"></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="handleSubmit">
              <div class="mb-3">
                <label class="form-label">Nama Lembaga</label>
                <input type="text" v-model="formData.nama" class="form-control" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Keterangan</label>
                <textarea v-model="formData.keterangan" class="form-control" rows="3"></textarea>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" @click="showModal = false">Batal</button>
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
  name: 'LembagaAkreditasi',
  data() {
    return {
      lembagaList: [],
      selectedLembaga: '',
      searchQuery: '',
      showModal: false,
      isEditing: false,
      formData: {
        la_id: '',
        nama: '',
        keterangan: ''
      },
      currentPage: 1,
      perPage: 10
    }
  },
  computed: {
    filteredList() {
      let filtered = this.lembagaList
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
    }
  },
  mounted() {
    this.fetchData()
  },
  methods: {
    async fetchData() {
      try {
        const response = await fetch('/api/la.php?action=get_la')
        const data = await response.json()
        console.log('Data lembaga:', data)
        this.lembagaList = data
      } catch (error) {
        console.error('Error fetching lembaga:', error)
      }
    },
    handleAdd() {
      this.isEditing = false
      this.formData = {
        la_id: '',
        nama: '',
        keterangan: ''
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
          ? `/api/la.php?action=update` 
          : '/api/la.php?action=add'

        const response = await fetch(url, {
          method: this.isEditing ? 'PUT' : 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify(this.formData)
        })

        if (response.ok) {
          this.showModal = false
          await this.fetchData()
          alert(this.isEditing ? 'Data berhasil diupdate!' : 'Data berhasil ditambahkan!')
        } else {
          const error = await response.json()
          alert('Gagal menyimpan data: ' + (error.error || ''))
        }
      } catch (error) {
        console.error('Error submitting data:', error)
        alert('Terjadi kesalahan saat menyimpan data')
      }
    },
    async handleDelete(id) {
      if (confirm('Apakah Anda yakin ingin menghapus data ini?')) {
        try {
          const response = await fetch(`/api/la.php?action=delete&id=${id}`, {
            method: 'DELETE'
          })
          if (response.ok) {
            await this.fetchData()
            alert('Data berhasil dihapus!')
          } else {
            const error = await response.json()
            alert('Gagal menghapus data: ' + (error.error || ''))
          }
        } catch (error) {
          console.error('Error deleting data:', error)
          alert('Terjadi kesalahan saat menghapus data')
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
.modal {
  background-color: rgba(0, 0, 0, 0.5);
}
</style>