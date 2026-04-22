// Master Data
export interface Ruangan {
    id: number;
    nama_ruangan: string;
    kapasitas: number;
    created_at?: string;
    updated_at?: string;
}

export interface Alat {
    id: number;
    nama_alat: string;
    satuan: string;
    total_stok: number;
    created_at?: string;
    updated_at?: string;
}

export interface JenisPengujian {
    id: number;
    nama_pengujian: string;
    deskripsi?: string;
    created_at?: string;
    updated_at?: string;
}

// User Profile Extend
export interface UserProfil {
    id: number;
    name: string;
    nim?: string;
    program_studi?: string;
    email: string;
    roles?: string[];
}

// Pengajuan Data
export interface DetailRuangan {
    ruangan?: string;
    kapasitas?: number;
    tanggal_mulai?: string;
    tanggal_selesai?: string;
    waktu_mulai?: string;
    waktu_selesai?: string;
    jumlah_pengguna?: number;
    catatan_alat_bahan?: string;
}

export interface DetailAlat {
    nama_alat?: string;
    jumlah_dipinjam?: number;
    tanggal_mulai?: string;
    tanggal_selesai?: string;
    keperluan_spesifik?: string;
}

export interface DetailPengujian {
    jenis_pengujian?: string;
    nama_sampel?: string;
    jumlah_sampel?: number;
    keterangan_tambahan?: string;
}

export interface Pengajuan {
    id: number;
    tipe_pengajuan: 'ruangan' | 'alat' | 'pengujian';
    status: 'diajukan' | 'diverifikasi' | 'disetujui' | 'ditolak' | 'selesai';
    nomor_hp?: string;
    judul_proyek: string;
    tujuan_penggunaan?: string;
    dosen_pembimbing?: string;
    email_dosen?: string;
    catatan_reviewer?: string;
    dibuat_pada: string;
    
    pengaju?: UserProfil;
    detail_ruangan?: DetailRuangan;
    detail_alat?: DetailAlat;
    detail_pengujian?: DetailPengujian;
}

// Ketersediaan Kalender
export interface KalenderEntry {
    tipe: 'ruangan' | 'alat' | 'pengujian';
    nama: string;
    status: string;
    waktu_mulai?: string;
    waktu_selesai?: string;
    tanggal_mulai?: string;
    tanggal_selesai?: string;
    detail_text?: string;
}

export interface KalenderResponse {
    bulan: string;
    filter: string;
    kalender: Record<string, KalenderEntry[]>;
}
