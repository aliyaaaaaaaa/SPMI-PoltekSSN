<template>
  <div class="container mt-4">
    <h2>Evaluasi Desk</h2>

    <!-- Filter Data Box -->
    <div class="card mb-3">
      <div class="card-body">
        <h5 class="card-title">Filter Data</h5>
        <div class="row">
          <div class="col-md-4 mb-3">
            <label>Tahun</label>
            <select class="form-select" v-model="selectedTahun">
              <option value="">Semua Tahun</option>
              <option v-for="tahun in tahunList" :key="tahun.tahun_id" :value="tahun.tahun_id">
                {{ tahun.nama }}
              </option>
            </select>
          </div>
          <div class="col-md-4 mb-3">
            <label>Lembaga Akreditasi</label>
            <select class="form-select" v-model="selectedLA">
              <option value="">Semua LA</option>
              <option v-for="la in laList" :key="la.la_id" :value="la.la_id">
                {{ la.nama }}
              </option>
            </select>
          </div>
          <div class="col-md-4 mb-3">
            <label>Program Studi</label>
            <select class="form-select" v-model="selectedProdi">
              <option value="">Semua Prodi</option>
              <option v-for="prodi in prodiList" :key="prodi.role_id" :value="prodi.role_id">
                {{ prodi.nama }}
              </option>
            </select>
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
    </div>

    <!-- Table -->
    <div class="table-responsive">
      <table class="table">
        <thead>
          <tr>
            <th>No</th>
            <th>Program Studi</th>
            <th>Tahun</th>
            <th>Lembaga Akreditasi</th>
            <th>Target Nilai</th>
            <th>Nilai Evaluasi Diri</th>
            <th>Nilai Evaluasi Desk</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(item, index) in paginatedList" :key="item.on_id">
            <td>{{ startIndex + index + 1 }}</td>
            <td>{{ item.prodi_nama }}</td>
            <td>{{ item.tahun_nama }}</td>
            <td>{{ item.la_nama }}</td>
            <td>{{ item.target }}</td>
            <td>{{ item.nilai_edi }}</td>
            <td>{{ item.nilai_edesk }}</td>
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
  </div>
</template>

<script>
export default {
  name: 'EvaluasiDiri',
  data() {
    return {
      nilaiList: [],
      tahunList: [],
      laList: [],
      prodiList: [],
      selectedTahun: '',
      selectedLA: '',
      selectedProdi: '',
      searchQuery: '',
      formData: {
        on_id: '',
        prodi_id: '',
        tahun_id: '',
        la_id: '',
        target: '',
        nilai_edi: '',
        nilai_edesk: ''
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

      // Filter berdasarkan LA yang dipilih
      if (this.selectedLA) {
        filtered = filtered.filter(item => item.la_id === this.selectedLA)
      }

      // Filter berdasarkan Prodi yang dipilih
      if (this.selectedProdi) {
        filtered = filtered.filter(item => item.prodi_id === this.selectedProdi)
      }

      // Filter berdasarkan search query
      if (this.searchQuery) {
        const query = this.searchQuery.toLowerCase()
        filtered = filtered.filter(item => 
          (item.prodi_nama && item.prodi_nama.toLowerCase().includes(query)) ||
          (item.tahun_nama && item.tahun_nama.toLowerCase().includes(query)) ||
          (item.la_nama && item.la_nama.toLowerCase().includes(query))
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
        const response = await fetch('/api/evaluasi.php?action=get_nilai')
        const data = await response.json()
        this.nilaiList = data
      } catch (error) {
        console.error('Error fetching nilai:', error)
      }
    },
    async fetchTahun() {
      try {
        const response = await fetch('/api/evaluasi.php?action=get_tahun')
        const data = await response.json()
        this.tahunList = data
      } catch (error) {
        console.error('Error fetching tahun:', error)
      }
    },
    async fetchLA() {
      try {
        const response = await fetch('/api/evaluasi.php?action=get_la')
        const data = await response.json()
        this.laList = data
      } catch (error) {
        console.error('Error fetching LA:', error)
      }
    },
    async fetchProdi() {
      try {
        const response = await fetch('/api/evaluasi.php?action=get_prodi')
        const data = await response.json()
        this.prodiList = data
      } catch (error) {
        console.error('Error fetching prodi:', error)
      }
    }
  },
    mounted() {
    this.fetchData()
    this.fetchTahun()
    this.fetchLA()
    this.fetchProdi()
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