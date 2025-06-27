<template>
  <div class="container mt-4">
    <h2>Jenis Temuan</h2>

    <!-- Filter Box -->
    <div class="card mb-3">
      <div class="card-body">
        <h5 class="card-title">Filter Data</h5>
        <div>
          <div>Kategori Temuan</div>
          <select v-model="selectedKategori" class="form-control" @change="handleKategoriChange">
            <option value="">Semua Kategori Temuan</option>
            <option v-for="kategori in kategoriList" :key="kategori.kt_id" :value="kategori.kt_id">
              {{ kategori.nama_kt }}
            </option>
          </select>
        </div>
      </div>
    </div>

    <!-- Search dan Tambah -->
    <div class="d-flex justify-content-between align-items-center mb-3">
      <div class="d-flex align-items-center gap-3">
        <div class="search-box">
          <input 
            type="text" 
            class="form-control" 
            placeholder="Cari jenis temuan..."
            v-model="searchQuery"
          >
        </div>
      </div>
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
            <th>Jenis Temuan</th>
            <th>Kategori Temuan</th>
            <th>Keterangan</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(item, index) in paginatedList" :key="item.jt_id">
            <td>{{ startIndex + index + 1 }}</td>
            <td>{{ item.nama_jt }}</td>
            <td>{{ item.nama_kt }}</td>
            <td>{{ item.keterangan || '-' }}</td>
            <td>
              <button class="btn btn-primary btn-sm me-2" @click="editItem(item)">Edit</button>
              <button class="btn btn-danger btn-sm" @click="deleteItem(item.jt_id)">Hapus</button>
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
            <h5 class="modal-title">{{ isEditing ? 'Edit' : 'Tambah' }} Jenis Temuan</h5>
            <button type="button" class="btn-close" @click="closeModal"></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="submitForm">
              <div class="mb-3">
                <label class="form-label">Kategori Temuan</label>
                <select v-model="formData.kt_id" class="form-select" required>
                  <option value="">-- PILIH --</option>
                  <option v-for="kt in ktList" :key="kt.kt_id" :value="kt.kt_id">
                    {{ kt.nama_kt }}
                  </option>
                </select>
              </div>
              <div class="mb-3">
                <label class="form-label">Nama Jenis Temuan</label>
                <input type="text" v-model="formData.nama_jt" class="form-control" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Keterangan</label>
                <textarea v-model="formData.keterangan" class="form-control"></textarea>
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
      jenisList: [],
      ktList: [],
      kategoriList: [],
      jenisOptions: [],
      selectedKategori: '',
      selectedJenis: '',
      searchQuery: '',
      showAddModal: false,
      isEditing: false,
      formData: {
        jt_id: '',
        kt_id: '',
        nama_jt: '',
        keterangan: ''
      },
      currentPage: 1,
      perPage: 10
    }
  },
  computed: {
    filteredList() {
      let filtered = this.jenisList

      // Filter berdasarkan kategori yang dipilih
      if (this.selectedKategori) {
        filtered = filtered.filter(item => item.kt_id === this.selectedKategori)
      }

      // Filter berdasarkan jenis yang dipilih
      if (this.selectedJenis) {
        filtered = filtered.filter(item => item.jt_id === this.selectedJenis)
      }

      // Filter berdasarkan pencarian
      if (this.searchQuery) {
        const query = this.searchQuery.toLowerCase()
        filtered = filtered.filter(item => 
          (item.nama_jt && item.nama_jt.toLowerCase().includes(query)) ||
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
  methods: {
    updateJenisOptions() {
      this.jenisOptions = [...new Set(this.jenisList.map(item => item.nama_jt))]
    },
    async fetchJenisTemuan() {
      try {
        const response = await fetch('/api/jenis_temuan.php?action=get_jenis')
        const data = await response.json()
        console.log('Data Jenis:', data)
        this.jenisList = data
      } catch (error) {
        console.error('Error fetching Jenis:', error)
      }
    },
    async fetchKategori() {
      try {
        const response = await fetch('/api/kt.php?action=get_kt')
        const data = await response.json()
        this.ktList = data
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
    async deleteItem(jtId) {
      if (confirm('Apakah Anda yakin ingin menghapus data ini?')) {
        try {
          const response = await fetch(`/api/jenis_temuan.php?action=delete&id=${jtId}`, {
            method: 'DELETE'
          })
          const result = await response.json()
          if (response.ok) {
            this.fetchJenisTemuan()
            alert('Data berhasil dihapus!')
          } else {
            alert('Gagal menghapus data: ' + result.error)
          }
        } catch (error) {
          console.error('Error deleting Jenis:', error)
          alert('Terjadi kesalahan saat menghapus data')
        }
      }
    },
    closeModal() {
      this.showAddModal = false
      this.isEditing = false
      this.formData = {
        jt_id: '',
        kt_id: '',
        nama_jt: '',
        keterangan: ''
      }
    },
    async submitForm() {
      try {
        const url = this.isEditing 
          ? '/api/jenis_temuan.php?action=update' 
          : '/api/jenis_temuan.php?action=add'

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
          this.fetchJenisTemuan()
          alert(this.isEditing ? 'Data berhasil diupdate!' : 'Data berhasil disimpan!')
        } else {
          alert('Gagal menyimpan data: ' + result.error)
        }
      } catch (error) {
        console.error('Error submitting form:', error)
        alert('Terjadi kesalahan saat menyimpan data')
      }
    },
    handleKategoriChange() {
      // Reset jenis selection when kategori changes
      this.selectedJenis = ''
      // Tidak perlu fetch ulang data, karena filtering sudah ditangani computed property
    },
  },
  mounted() {
    this.fetchJenisTemuan()
    this.fetchKategori()
    this.updateJenisOptions()
  }
}
</script>

<style scoped>
.modal {
  background-color: rgba(0, 0, 0, 0.5);
}
.search-box {
  width: 300px;
}
.card-title {
  margin-bottom: 1rem;
}
</style> 