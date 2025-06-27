<template>
  <div class="container mt-4">
    <h2 class="mb-4 fw-bold">Manajemen Dokumen</h2>
    <!-- Filter Section -->
    <div class="card mb-4">
      <div class="card-body">
        <h5 class="fw-bold mb-3">Filter Data</h5>
        <div class="row">
          <div class="col-md-3 mb-2">
            <label class="form-label">Kategori Dokumen</label>
            <select v-model="filter.kategori" class="form-select" @change="fetchDokumen">
              <option value="">Semua Kategori</option>
              <option v-for="k in kategoriList" :key="k.kd_id" :value="k.kd_id">{{ k.nama_kd }}</option>
            </select>
          </div>
          <div class="col-md-3 mb-2">
            <label class="form-label">Jenis Dokumen</label>
            <select v-model="filter.jenis" class="form-select" @change="fetchDokumen">
              <option value="">Semua Jenis</option>
              <option v-for="j in jenisList" :key="j.id" :value="j.id">{{ j.nama }}</option>
            </select>
          </div>
          <div class="col-md-3 mb-2">
            <label class="form-label">Tahun</label>
            <input v-model="filter.tahun" class="form-control" placeholder="Tahun" @input="fetchDokumen" />
          </div>
          <div class="col-md-3 mb-2">
            <label class="form-label">Pengunggah</label>
            <select v-model="filter.unit" class="form-select" @change="fetchDokumen">
              <option value="">Semua Pengunggah</option>
              <option v-for="u in unitList" :key="u.id" :value="u.value">{{ u.label }}</option>
            </select>
          </div>
          <div class="col-md-3 mb-2">
            <label class="form-label">Prodi</label>
            <select v-model="filter.prodi" class="form-select" @change="fetchDokumen">
              <option value="">Semua Prodi</option>
              <option v-for="p in prodiList" :key="p.role_id" :value="p.role_id">{{ p.nama }}</option>
            </select>
          </div>
        </div>
      </div>
    </div>
    <!-- Baris search, per page, dan tombol tambah -->
    <div class="row align-items-center mb-3">
      <div class="col-md-6 mb-2 d-flex gap-2">
        <input v-model="search" @input="goToPage(1)" type="text" class="form-control" placeholder="Cari dokumen..." />
        <span class="me-2">Per page:</span>
          <select v-model="perPage" class="form-select" style="width: auto;">
            <option value="10">10</option>
            <option value="25">25</option>
            <option value="50">50</option>
          </select>
      </div>
      <div class="col-md-6 mb-2 text-end">
        <button class="btn btn-primary" @click="showModal = true">Tambah</button>
      </div>
    </div>
    <!-- Table -->
    <div class="table-responsive">
      <table class="table table-bordered align-middle">
        <thead class="table-light">
          <tr>
            <th style="width:40px;">No</th>
            <th>Nama File</th>
            <th style="width:80px;">Tahun</th>
            <th>Prodi</th>
            <th>Kategori</th>
            <th>Jenis</th>
            <th>Pengunggah</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(d, i) in paginatedDokumen" :key="d.dokumen_id">
            <td>{{ startEntry + i }}</td>
            <td>{{ d.nama_file }}</td>
            <td>{{ d.tahun }}</td>
            <td>
              <span v-if="d.nama_prodi">{{ d.nama_prodi }}</span>
              <span v-else>-</span>
            </td>
            <td>{{ d.nama_kd }}</td>
            <td>{{ d.tipe_dokumen }}</td>
            <td>{{ getPengunggahLabel(d.user_id) }}</td>
            <td>
              <a
              class="btn btn-success btn-sm me-1"
              :href="`/uploads/${d.path_file}`"
              :download="d.nama_file"
              >
                Unduh
              </a>
              <button class="btn btn-info btn-sm me-1" @click="lihat(d)">Lihat</button>
              <button class="btn btn-warning btn-sm" @click="edit(d.dokumen_id)">Edit</button>
              <button class="btn btn-danger btn-sm ms-1" @click="hapus(d.dokumen_id)">Hapus</button>
            </td>
          </tr>
          <tr v-if="paginatedDokumen.length === 0">
            <td colspan="7" class="text-center text-muted">Tidak ada data dokumen.</td>
          </tr>
        </tbody>
      </table>
    </div>
    <!-- Pagination -->
    <div class="d-flex justify-content-between align-items-center mt-3">
        Showing {{ startEntry }} to {{ endEntry }} of {{ filteredDokumen.length }} entries
      <nav>
        <ul class="pagination">
          <li class="page-item" :class="{disabled: page === 1}">
            <button class="page-link" @click="goToPage(page-1)" :disabled="page === 1">Previous</button>
          </li>
          <li class="page-item active">
            <span class="page-link">{{ page }}</span>
          </li>
          <li class="page-item" :class="{disabled: endEntry >= filteredDokumen.length}">
            <button class="page-link" @click="goToPage(page+1)" :disabled="endEntry >= filteredDokumen.length">Next</button>
          </li>
        </ul>
      </nav>
    </div>

    <!-- Modal Tambah/Edit -->
    <div class="modal" tabindex="-1" :class="{ 'd-block': showModal || showEditModal }" v-if="showModal || showEditModal">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <form @submit.prevent="showEditModal ? update() : tambah()">
            <div class="modal-header">
              <h5 class="modal-title">{{ showEditModal ? 'Edit Dokumen' : 'Tambah Data Dokumen' }}</h5>
              <button type="button" class="btn-close" @click="closeModal"></button>
            </div>
            <div class="modal-body">
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="form-label">Kategori Dokumen</label>
                  <select v-model="currentForm.kategori_id" class="form-select" required>
                    <option value="">-- PILIH --</option>
                    <option v-for="k in kategoriList" :key="k.kd_id" :value="k.kd_id">{{ k.nama_kd }}</option>
                  </select>
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label">Jenis Dokumen</label>
                  <select v-model="currentForm.jenis_id" class="form-select" required>
                    <option value="">-- PILIH --</option>
                    <option v-for="j in jenisList" :key="j.id" :value="j.id">{{ j.nama }}</option>
                  </select>
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label">Tahun</label>
                  <input v-model="currentForm.tahun" class="form-control" required />
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label">Pengunggah</label>
                  <select v-model="currentForm.user_id" class="form-select" required>
                    <option value="">-- PILIH --</option>
                    <option v-for="u in unitList" :key="u.id" :value="u.value.split('_')[1]">{{ u.label }}</option>
                  </select>
                </div>
                <div class="col-md-12 mb-3">
                  <label class="form-label">Prodi</label>
                  <div class="row">
                    <div class="col-md-4" v-for="p in prodiList" :key="p.role_id">
                      <div class="form-check">
                        <input class="form-check-input"
                              type="checkbox"
                              :id="'prodi_'+p.role_id"
                              :value="p.role_id"
                              v-model="currentForm.prodi_ids">
                        <label class="form-check-label" :for="'prodi_'+p.role_id">
                          {{ p.nama }}
                        </label>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="col-md-12 mb-3">
                  <label class="form-label">File</label>
                  <input type="file" @change="onFileChangeEdit" class="form-control" :required="!showEditModal || !currentForm.old_path_file" />
                  <small class="text-muted">* file .PDF, maksimal 2 MB</small>
                  <div v-if="showEditModal && currentForm.old_path_file" class="mt-2">
                    File saat ini: <a :href="`/Dokumen/${currentForm.old_path_file}`" target="_blank">{{ currentForm.old_path_file }}</a>
                  </div>
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <button type="submit" class="btn btn-primary">{{ showEditModal ? 'Update' : 'Simpan' }}</button>
              <button type="button" class="btn btn-danger" @click="closeModal">Batal</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Modal Lihat -->
    <div class="modal" tabindex="-1" :class="{ 'd-block': showLihatModal }" v-if="showLihatModal">
      <div class="modal-dialog modal-xl">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Lihat Dokumen</h5>
            <button type="button" class="btn-close" @click="showLihatModal = false"></button>
          </div>
          <div class="modal-body">
            <iframe v-if="lihatFile" :src="`/Dokumen/${lihatFile}`" width="100%" height="600px"></iframe>
            <div v-else class="text-center text-muted">File tidak ditemukan.</div>
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
      dokumenList: [],
      kategoriList: [],
      jenisList: [],
      unitList: [],
      filter: { kategori: '', jenis: '', tahun: '', unit: '' },
      search: '',
      perPage: 10,
      page: 1,
      showModal: false,
      showEditModal: false,
      showLihatModal: false,
      lihatFile: '',
      prodiList: [],
      form: { kategori_id: '', jenis_id: '', tahun: '', user_id: '', file: null, prodi_ids: [] },
      editForm: { dokumen_id: '', kategori_id: '', jenis_id: '', tahun: '', user_id: '', file: null, old_path_file: '', nama_file: '', prodi_ids: [] }
    }
  },
  computed: {
    currentForm() {
      return this.showEditModal ? this.editForm : this.form;
    },
    filteredDokumen() {
      // Filter dokumenList berdasarkan search box (nama file, tahun, kategori, pengunggah, jenis)
      if (!this.search) return this.dokumenList;
      const s = this.search.toLowerCase();
      return this.dokumenList.filter(d =>
        (d.nama_file && d.nama_file.toLowerCase().includes(s)) ||
        (d.nama_prodi && d.nama_prodi.toLowerCase().includes(s)) ||
        (d.tahun && String(d.tahun).includes(s)) ||
        (d.nama_kd && d.nama_kd.toLowerCase().includes(s)) ||
        (d.tipe_dokumen && d.tipe_dokumen.toLowerCase().includes(s)) ||
        (this.getPengunggahLabel(d.user_id).toLowerCase().includes(s))
      );
    },
    paginatedDokumen() {
      const start = (this.page - 1) * this.perPage;
      return this.filteredDokumen.slice(start, start + this.perPage);
    },
    startEntry() {
      return this.filteredDokumen.length === 0 ? 0 : (this.page - 1) * this.perPage + 1;
    },
    endEntry() {
      const end = this.page * this.perPage;
      return end > this.filteredDokumen.length ? this.filteredDokumen.length : end;
    }
  },
  methods: {
    async fetchDokumen() {
      const params = new URLSearchParams(this.filter).toString();
      const res = await fetch(`/api/dokumen.php?action=list&${params}`);
      this.dokumenList = await res.json();
      this.goToPage(1); // Reset ke halaman 1 setiap filter berubah
    },
    goToPage(p) {
      if (p < 1) p = 1;
      const maxPage = Math.ceil(this.filteredDokumen.length / this.perPage) || 1;
      if (p > maxPage) p = maxPage;
      this.page = p;
    },
    async fetchKategoriJenisUnitProdi() {
      const [k, j, u, p] = await Promise.all([
        fetch('/api/dokumen.php?action=kategori'),
        fetch('/api/dokumen.php?action=jenis'),
        fetch('/api/dokumen.php?action=unit'),
        fetch('/api/dokumen.php?action=prodi')
      ]);
      this.kategoriList = await k.json();
      this.jenisList = await j.json();
      this.unitList = await u.json();
      this.prodiList = await p.json();
    },
    onFileChange(e) {
      this.form.file = e.target.files[0];
    },
    onFileChangeEdit(e) {
      this.editForm.file = e.target.files[0];
    },
    async tambah() {
      const fd = new FormData();
      Object.keys(this.form).forEach(k => {
        if (this.form[k]) fd.append(k, this.form[k]);
      });
      if (this.form.prodi_ids) {
        this.form.prodi_ids.forEach(pid => fd.append('prodi_ids[]', pid));
      }
      if (this.form.file) fd.append('file', this.form.file);
      await fetch('/api/dokumen.php?action=add', { method: 'POST', body: fd });
      this.showModal = false;
      this.form = { kategori_id: '', jenis_id: '', tahun: '', user_id: '', file: null };
      this.fetchDokumen();
    },
    async edit(id) {
      const res = await fetch(`/api/dokumen.php?action=get&id=${id}`);
      const data = await res.json();
      this.editForm = {
        dokumen_id: data.dokumen_id,
        kategori_id: data.kd_id,
        jenis_id: data.tipe_dokumen,
        tahun: data.tahun,
        user_id: data.user_id,
        file: null,
        old_path_file: data.path_file,
        nama_file: data.nama_file,
        prodi_ids: data.prodi_ids || []
      };
      this.showEditModal = true;
    },
    async update() {
      const fd = new FormData();
      Object.keys(this.editForm).forEach(k => {
        if (this.editForm[k] !== null && this.editForm[k] !== undefined) fd.append(k, this.editForm[k]);
      });
      if (this.editForm.prodi_ids) {
        this.editForm.prodi_ids.forEach(pid => fd.append('prodi_ids[]', pid));
      }
      if (this.editForm.file) fd.append('file', this.editForm.file);
      await fetch('/api/dokumen.php?action=update', { method: 'POST', body: fd });
      this.showEditModal = false;
      this.editForm = { dokumen_id: '', kategori_id: '', jenis_id: '', tahun: '', user_id: '', file: null, old_path_file: '', nama_file: '', nama_prodi: '' };
      this.fetchDokumen();
    },
    async hapus(id) {
      if (!confirm('Hapus dokumen ini?')) return;
      await fetch(`/api/dokumen.php?action=delete&id=${id}`);
      this.fetchDokumen();
    },
    unduh(d) {
      window.open(`/Dokumen/${d.path_file}`, '_blank');
    },
    lihat(d) {
      this.lihatFile = d.path_file;
      this.showLihatModal = true;
    },
    closeModal() {
      this.showModal = false;
      this.showEditModal = false;
      this.editForm = { dokumen_id: '', kategori_id: '', jenis_id: '', tahun: '', user_id: '', file: null, old_path_file: '', nama_file: '', prodi_id: '' };
      this.form = { kategori_id: '', jenis_id: '', tahun: '', user_id: '', file: null };
    },
    getPengunggahLabel(user_id) {
      const unit = this.unitList.find(u => u.value.endsWith('_' + user_id));
      return unit ? unit.label : '-';
    }
  },
  mounted() {
    this.fetchKategoriJenisUnitProdi();
    this.fetchDokumen();
  }
}
</script>