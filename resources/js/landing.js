import {
    ArcElement,
    BarController,
    BarElement,
    CategoryScale,
    Chart,
    DoughnutController,
    Legend,
    LinearScale,
    Tooltip,
} from 'chart.js';

Chart.register(
    ArcElement,
    BarController,
    BarElement,
    CategoryScale,
    DoughnutController,
    Legend,
    LinearScale,
    Tooltip,
);

const stats = JSON.parse(document.getElementById('landing-stats').textContent);
const safely = (fn) => { try { fn(); } catch (e) { console.error('Gagal menggambar grafik:', e); } };
const palette = ['#dc2626', '#f59e0b', '#10b981', '#0ea5e9', '#6366f1', '#d946ef', '#64748b', '#84cc16'];
const nf = new Intl.NumberFormat('id-ID');

Chart.defaults.font.family = 'Nunito, system-ui, sans-serif';
Chart.defaults.plugins.tooltip.callbacks.label = (ctx) => {
    const value = ctx.parsed.x ?? ctx.parsed.y ?? ctx.parsed;
    return ` ${ctx.dataset.label ? ctx.dataset.label + ': ' : ctx.label + ': '}${nf.format(value)}`;
};

const draw = (id, config) => {
    const canvas = document.getElementById(id);
    if (canvas) new Chart(canvas, config);
};
const doughnut = (id, labels, data) => draw(id, {
    type: 'doughnut',
    data: { labels, datasets: [{ data, backgroundColor: palette, borderWidth: 2 }] },
    options: { maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } },
});
const hbar = (id, labels, datasets, stacked = false) => draw(id, {
    type: 'bar',
    data: { labels, datasets },
    options: {
        indexAxis: 'y',
        maintainAspectRatio: false,
        scales: { x: { stacked, ticks: { callback: (v) => nf.format(v) } }, y: { stacked, ticks: { autoSkip: false } } },
        plugins: { legend: { display: datasets.length > 1 } },
    },
});

const provinces = stats.provinces;
const byDesa = [...provinces].sort((a, b) => b.desa - a.desa);
const byKab = [...provinces].sort((a, b) => (b.kabupaten + b.kota) - (a.kabupaten + a.kota));

safely(() => hbar('chart-desa-provinsi', byDesa.map((p) => p.name), [
    { label: 'Desa/Kelurahan', data: byDesa.map((p) => p.desa), backgroundColor: '#dc2626' },
]));

safely(() => hbar('chart-kab-provinsi', byKab.map((p) => p.name), [
    { label: 'Kabupaten', data: byKab.map((p) => p.kabupaten), backgroundColor: '#0ea5e9' },
    { label: 'Kota', data: byKab.map((p) => p.kota), backgroundColor: '#f59e0b' },
], true));

safely(() => doughnut('chart-region', stats.regions.map((r) => r.name), stats.regions.map((r) => r.desa)));
safely(() => doughnut('chart-tipe', ['Desa', 'Kelurahan', 'Lainnya'], [stats.types.desa, stats.types.kelurahan, stats.types.lainnya]));
safely(() => doughnut('chart-kab-kota', ['Kabupaten', 'Kota'], [stats.totals.kabupaten, stats.totals.kota]));

safely(() => hbar('chart-top-kab', stats.topKabupaten.map((k) => k.name), [
    { label: 'Desa/Kelurahan', data: stats.topKabupaten.map((k) => k.total), backgroundColor: '#6366f1' },
]));
