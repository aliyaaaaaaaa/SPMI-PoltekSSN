<template>
  <div class="container mt-4">
    <h2>Standar</h2>
    <!-- Search dan Tambah -->
    <div class="d-flex justify-content-between mb-3">
      <div class="search-box">
        <input 
          type="text" 
          class="form-control" 
          placeholder="Cari standar..."
          v-model="searchQuery"
        >
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
            <th>Nama Standar</th>
            <th>Keterangan</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(item, index) in filteredList" :key="item.psm_id">
            <td>{{ index + 1 }}</td>
            <td>{{ item.nama }}</td>
            <td>{{ item.keterangan || '-' }}</td>
            <td>
              <button class="btn btn-primary btn-sm me-2" @click="editItem(item)">Edit</button>
              <button class="btn btn-danger btn-sm" @click="deleteItem(item.psm_id)">Hapus</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal Form -->
    <div class="modal" tabindex="-1" :class="{ 'd-block': showAddModal }">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ isEditing ? 'Edit' : 'Tambah' }} Standar</h5>
            <button type="button" class="btn-close" @click="closeModal"></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="submitForm">
              <div class="mb-3">
                <label class="form-label">Nama Standar</label>
                <input type="text" v-model="formData.nama" class="form-control" required>
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
  name: 'Standar',
  data() {
    return {
      standarList: [],
      standarOptions: [],
      selectedStandar: '',
      searchQuery: '',
      showAddModal: false,
      isEditing: false,
      formData: {
        psm_id: '',
        nama: '',
        keterangan: ''
      }
    }
  },
  computed: {
    filteredList() {
      let filtered = this.standarList

      if (this.selectedStandar) {
        filtered = filtered.filter(item => item.nama === this.selectedStandar)
      }
      if (this.searchQuery) {
        const query = this.searchQuery.toLowerCase()
        filtered = filtered.filter(item => 
          item.nama.toLowerCase().includes(query) ||
          (item.keterangan && item.keterangan.toLowerCase().includes(query))
        )
      }
      return filtered
    }
  },
  mounted() {
    this.fetchData()
    this.updateStandarOptions()
  },
  methods: {
    updateStandarOptions() {
      this.standarOptions = [...new Set(this.standarList.map(item => item.nama))]
    },
    async fetchData() {
      try {
        const response = await fetch('/api/standar.php?action=get_standar')
        const data = await response.json()
        console.log('Data Standar:', data)
        this.standarList = data
      } catch (error) {
        console.error('Error fetching Standar:', error)
      }
    },
    editItem(item) {
      this.isEditing = true
      this.formData = { ...item }
      this.showAddModal = true
    },
    async deleteItem(psmId) {
      if (confirm('Apakah Anda yakin ingin menghapus data ini?')) {
        try {
          const response = await fetch(`/api/standar.php?action=delete&id=${psmId}`, {
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
          console.error('Error deleting Standar:', error)
          alert('Terjadi kesalahan saat menghapus data')
        }
      }
    },
    closeModal() {
      this.showAddModal = false
      this.isEditing = false
      this.formData = {
        psm_id: '',
        nama: '',
        keterangan: ''
      }
    },
    async submitForm() {
      try {
        const url = this.isEditing 
          ? '/api/standar.php?action=update' 
          : '/api/standar.php?action=add'

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
.card-title {
  margin-bottom: 1rem;
}
.modal {
  background-color: rgba(0, 0, 0, 0.5);
}
</style> 