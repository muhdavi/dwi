import './admin-jquery';
import 'bootstrap';
import 'admin-lte/dist/js/adminlte';

// Dropdown berantai provinsi → kabupaten → kecamatan pada form tambah data.
// Nilai induk langsung dikirim lewat select terakhir (name="parent_id").
const cascade = document.querySelector('[data-cascade]');

if (cascade) {
    const api = cascade.dataset.api;
    const selects = [...cascade.querySelectorAll('select[data-level]')];
    const prefixLength = [2, 4, 6];
    const initial = cascade.dataset.initial || '';

    const endpoint = (level, parent) => ({
        provinsi: `${api}/provinsi`,
        kabupaten: `${api}/provinsi/${parent}/kabupaten`,
        kecamatan: `${api}/kabupaten/${parent}/kecamatan`,
    }[level]);

    const fill = async (index, parent, selected) => {
        const select = selects[index];
        select.innerHTML = '<option value="">Memuat...</option>';
        select.disabled = true;

        const res = await fetch(endpoint(select.dataset.level, parent), { headers: { Accept: 'application/json' } });
        const { data } = await res.json();

        select.innerHTML = '<option value="">— Pilih —</option>' + data
            .map((r) => `<option value="${r.id}">${r.kode} — ${r.nama}</option>`)
            .join('');
        select.disabled = false;

        if (selected) {
            select.value = selected;
        }
    };

    const reset = (from) => {
        for (let i = from; i < selects.length; i++) {
            selects[i].innerHTML = '<option value="">— Pilih —</option>';
            selects[i].disabled = true;
        }
    };

    selects.forEach((select, i) => {
        select.addEventListener('change', () => {
            reset(i + 1);
            if (select.value && selects[i + 1]) {
                fill(i + 1, select.value);
            }
            const last = selects[selects.length - 1];
            document.querySelector('[data-kode-prefix]').textContent = last.value || '';
        });
    });

    (async () => {
        reset(1);
        await fill(0, null, initial.slice(0, prefixLength[0]));
        for (let i = 1; i < selects.length && initial.length >= prefixLength[i - 1]; i++) {
            await fill(i, initial.slice(0, prefixLength[i - 1]), initial.slice(0, prefixLength[i]));
        }
        document.querySelector('[data-kode-prefix]').textContent = initial;
    })();
}

document.querySelectorAll('form[data-confirm]').forEach((form) => {
    form.addEventListener('submit', (e) => {
        if (! window.confirm(form.dataset.confirm)) {
            e.preventDefault();
        }
    });
});
