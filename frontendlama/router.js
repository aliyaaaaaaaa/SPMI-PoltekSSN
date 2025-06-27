// 📄 router.js
import { createRouter, createWebHistory } from 'vue-router'
import AdminDashboard from './pages/AdminDashboard.vue'
import AuditorDashboard from './pages/AuditorDashboard.vue'
import AuditeeDashboard from './pages/AuditeeDashboard.vue'
import NilaiMutu from './pages/NilaiMutu.vue'
import Periode from './pages/Periode.vue'
import TargetNilai from './pages/TargetNilai.vue'
import Edi from './pages/Edi.vue'
import Edesk from './pages/Edesk.vue'
import LembagaAkreditasi from './pages/LembagaAkreditasi.vue'
import Tahun from './pages/Tahun.vue'
import Standar from './pages/Standar.vue'
import KategoriTemuan from './pages/KategoriTemuan.vue'
import JenisTemuan from './pages/JenisTemuan.vue'
import JenisDokumen from './pages/JenisDokumen.vue'
import KategoriDokumen from './pages/KategoriDokumen.vue'
import Auditee from './pages/Auditee.vue'
import GKM from './pages/GKM.vue'
import Auditor from './pages/Auditor.vue'
import DaftarKegiatan from './pages/DaftarKegiatan.vue'
import EvaluasiDeskAuditor from './pages/EvaluasiDeskAuditor.vue'
import EvaluasiDiriAuditee from './pages/EvaluasiDiriAuditee.vue'
import ManajemenDokumen from './pages/ManajemenDokumen.vue'

const routes = [
  {path: '/admin', component: AdminDashboard},
  {path: '/auditordashboard', component: AuditorDashboard},
  {path: '/auditeedashboard', component: AuditeeDashboard},
  {path: '/nilai-mutu', component: NilaiMutu},
  {path: '/periode', component: Periode},
  {path: '/target-nilai', component: TargetNilai},
  {path: '/evaluasidiri', component: Edi},
  {path: '/evaluasidesk', component: Edesk},
  {path: '/lembaga-akreditasi', component: LembagaAkreditasi},
  {path: '/tahun', component: Tahun},
  {path: '/standar', component: Standar},
  {path: '/kategori-temuan', component: KategoriTemuan},
  {path: '/jenis-temuan', component: JenisTemuan},
  {path: '/kategori-dokumen', component: KategoriDokumen},
  {path: '/jenis-dokumen', component: JenisDokumen},
  {path: '/auditee', component: Auditee},
  {path: '/gkm', component: GKM},
  {path: '/auditor', component: Auditor},
  {path: '/daftarkegiatan', component: DaftarKegiatan},
  {path: '/evaluasideskauditor', component: EvaluasiDeskAuditor 
    //, meta: { requiresAuth: true, roles: ['auditor'] }
  },
  {path: '/evaluasidiriauditee', component: EvaluasiDiriAuditee 
    //, meta: { requiresAuth: true, roles: ['auditor'] }
  },
  {path: '/manajemen-dokumen', component: ManajemenDokumen}
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

export default router;