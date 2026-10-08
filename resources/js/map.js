import * as L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import '../css/map.css';

const labels = { kawasan: 'Kawasan', bumdes: 'Kantor BUMDes', informasi: 'Titik informasi', potensi: 'Potensi desa' };
const letters = { bumdes: 'B', informasi: 'I', potensi: 'P' };

function popup(properties) {
    const box = document.createElement('div');
    const heading = document.createElement('strong');
    heading.textContent = properties.name;
    const description = document.createElement('p');
    description.textContent = properties.summary;
    box.append(heading, description);
    if (properties.url && properties.url.startsWith('/') && !properties.url.startsWith('//')) {
        const link = document.createElement('a');
        link.href = properties.url;
        link.textContent = properties.layer === 'bumdes' ? 'Website BUMDes →' : 'Detail potensi →';
        box.append(link);
    }
    return box;
}

export function initializeMaps() {
    document.querySelectorAll('[data-map-root]').forEach(initializeMap);
}

function initializeMap(root) {
    const canvas = root.querySelector('[data-map-canvas]');
    const status = root.querySelector('[data-map-status]');
    const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const controller = new AbortController();
    const listen = (node, event, callback) => node?.addEventListener(event, callback, { signal: controller.signal });
    let map;
    try {
        map = L.map(canvas, {
            scrollWheelZoom: false, doubleClickZoom: !root.dataset.mapEditor,
            zoomAnimation: !reduced, fadeAnimation: !reduced, markerZoomAnimation: !reduced,
            inertia: !reduced, minZoom: 3, maxZoom: 19,
        }).setView([-7.63, 111.52], 11);
        L.tileLayer(root.dataset.tileUrl, {
            attribution: root.dataset.tileAttribution,
            maxZoom: 19,
        }).on('tileerror', () => { root.querySelector('[data-map-tile-error]').hidden = false; }).addTo(map);
        if (root.dataset.mapEditor) {
            initializeEditor(root, map, listen);
        } else {
            initializeExplorer(root, map, listen, status);
        }
    } catch {
        status.textContent = 'Peta belum dapat dimuat. Muat ulang halaman; daftar informasi tetap tersedia.';
        map?.remove();
        controller.abort();
        return;
    }

    let frame;
    const observer = new ResizeObserver(() => {
        cancelAnimationFrame(frame);
        frame = requestAnimationFrame(() => map.invalidateSize({ animate: false }));
    });
    observer.observe(canvas);
    listen(window, 'keydown', (event) => {
        if (event.key === 'Escape') {
            map.closePopup();
        }
    });
    listen(window, 'pagehide', (event) => {
        if (!event.persisted) {
            observer.disconnect();
            cancelAnimationFrame(frame);
            map.remove();
            controller.abort();
        }
    });
    listen(window, 'pageshow', () => map.invalidateSize({ animate: false }));
}

function initializeExplorer(root, map, listen, status) {
    const collection = JSON.parse(root.querySelector('[data-map-collection]').textContent);
    const groups = Object.fromEntries(Object.keys(labels).map((key) => [key, L.featureGroup().addTo(map)]));
    const features = new Map();
    collection.features.forEach((feature) => {
        const properties = feature.properties;
        const layer = L.geoJSON(feature, {
            style: { color: properties.color, weight: 2, fillOpacity: 0.15, dashArray: properties.categories.length > 1 ? '8 5' : null },
            pointToLayer: (_feature, latlng) => L.marker(latlng, {
                title: properties.name,
                icon: (() => {
                    const pin = document.createElement('span');
                    pin.textContent = letters[properties.layer];
                    pin.style.backgroundColor = properties.color;
                    return L.divIcon({ className: 'geo-pin', html: pin, iconSize: [30, 36], iconAnchor: [15, 36] });
                })(),
            }),
        }).bindPopup(popup(properties));
        layer.addTo(groups[properties.layer]);
        features.set(feature.id, layer);
    });
    const bounds = L.featureGroup(Object.values(groups)).getBounds();
    if (bounds.isValid()) {
        map.fitBounds(bounds, { padding: [28, 28], maxZoom: 15, animate: false });
    }

    function filter() {
        const visible = new Set();
        root.querySelectorAll('[data-map-layer]').forEach((input) => {
            const group = groups[input.dataset.mapLayer];
            if (input.checked) {
                visible.add(input.dataset.mapLayer);
                group.addTo(map);
            } else {
                map.removeLayer(group);
            }
        });
        let count = 0;
        root.querySelectorAll('[data-map-item]').forEach((item) => {
            item.hidden = !visible.has(item.dataset.featureLayer);
            if (!item.hidden) count++;
        });
        root.querySelector('[data-map-empty]').hidden = count > 0;
        status.textContent = count + ' lokasi pada layer yang dipilih. Peta ini hanya-baca; tidak mengubah data.';
    }
    root.querySelectorAll('[data-map-layer]').forEach((input) => listen(input, 'change', filter));
    root.querySelectorAll('[data-map-focus]').forEach((button) => {
        button.disabled = false;
        listen(button, 'click', () => {
            const layer = features.get(button.dataset.mapFocus);
            if (!layer) return;
            map.fitBounds(layer.getBounds(), { padding: [40, 40], maxZoom: 16, animate: false });
            layer.openPopup();
            canvasFocus(root);
        });
    });
    filter();
}

function canvasFocus(root) {
    const canvas = root.querySelector('[data-map-canvas]');
    canvas.focus({ preventScroll: true });
    canvas.scrollIntoView({ block: 'nearest', behavior: 'instant' });
}

function initializeEditor(root, map, listen) {
    const type = root.dataset.mapEditor;
    const input = root.querySelector('[data-map-input]');
    const form = root.closest('form');
    const save = form.querySelector('[data-map-save]');
    const status = root.querySelector('[data-map-status]');
    const vertexSelect = root.querySelector('[data-map-vertex]');
    const drawings = L.layerGroup().addTo(map);
    let points = [];
    let closed = type === 'Point';
    let touched = false;

    try {
        const geometry = input.value ? JSON.parse(input.value) : null;
        if (geometry?.type === type && Array.isArray(geometry.coordinates)) {
            points = type === 'Point' ? [geometry.coordinates] : geometry.coordinates[0].slice(0, -1);
            if (!points.every((point) => Array.isArray(point) && point.length === 2 && point.every(Number.isFinite))) throw new Error();
            closed = true;
        } else if (geometry !== null) {
            throw new Error();
        }
    } catch {
        points = [];
        status.textContent = 'Geometri sebelumnya tidak valid. Pilih ulang lokasi pada peta.';
    }

    const icon = (number) => L.divIcon({ className: 'geo-editor-pin', html: '<span>' + number + '</span>', iconSize: [28, 28], iconAnchor: [14, 14] });
    const coordinate = (latlng) => [Number(latlng.lng.toFixed(6)), Number(latlng.lat.toFixed(6))];
    const ready = () => type === 'Point' || points.length === 0 || (closed && points.length >= 3);
    function sync(message) {
        drawings.clearLayers();
        const latlngs = points.map((point) => [point[1], point[0]]);
        if (type === 'Polygon' && points.length > 1) {
            (closed ? L.polygon(latlngs, { color: '#047857', fillOpacity: 0.15 }) : L.polyline(latlngs, { color: '#047857' })).addTo(drawings);
        }
        points.forEach((point, index) => {
            const marker = L.marker([point[1], point[0]], { draggable: true, title: type === 'Point' ? 'Lokasi terpilih' : 'Simpul ' + (index + 1), icon: icon(type === 'Point' ? '●' : index + 1) }).addTo(drawings);
            marker.on('dragend', () => {
                points[index] = coordinate(marker.getLatLng());
                touched = true;
                sync('Lokasi diperbarui. Tekan Simpan untuk menyimpan perubahan.');
            });
        });
        let geometry = null;
        if (points.length && ready()) {
            geometry = type === 'Point' ? { type, coordinates: points[0] } : { type, coordinates: [[...points, points[0]]] };
        }
        input.value = geometry ? JSON.stringify(geometry) : '';
        save.disabled = !ready();
        root.querySelectorAll('button').forEach((button) => { button.disabled = false; });
        if (type === 'Polygon') {
            root.querySelector('[data-map-finish]').disabled = closed || points.length < 3;
            root.querySelector('[data-map-undo]').disabled = points.length === 0;
            root.querySelector('[data-map-add]').disabled = closed && points.length > 0;
            root.querySelector('[data-map-move]').disabled = points.length === 0;
            const selected = vertexSelect.value;
            vertexSelect.replaceChildren(...points.map((_point, index) => {
                const option = document.createElement('option');
                option.value = String(index);
                option.textContent = 'Simpul ' + (index + 1);
                return option;
            }));
            if (!points.length) {
                const option = document.createElement('option');
                option.textContent = 'Belum ada simpul';
                option.value = '';
                vertexSelect.append(option);
            }
            vertexSelect.disabled = points.length === 0;
            vertexSelect.value = [...vertexSelect.options].some((option) => option.value === selected) ? selected : vertexSelect.options[0].value;
        }
        status.textContent = message || (points.length ? (type === 'Polygon' ? points.length + ' simpul · ' + (closed ? 'poligon selesai.' : 'belum selesai.') : 'Pin lokasi sudah dipilih.') : 'Belum ada lokasi. Klik peta atau gunakan tombol di tengah peta.');
    }

    function add(latlng) {
        if (type === 'Polygon' && closed && points.length) {
            status.textContent = 'Untuk menambah simpul, pilih Gambar ulang; untuk koreksi, geser simpul yang ada.';
            return;
        }
        if (type === 'Polygon' && points.length >= 500) {
            status.textContent = 'Batas maksimal 500 simpul tercapai.';
            return;
        }
        const point = coordinate(latlng);
        if (type === 'Polygon' && points.some((existing) => existing[0] === point[0] && existing[1] === point[1])) {
            status.textContent = 'Simpul sudah ada. Pilih posisi lain; gunakan Selesaikan poligon untuk menutup batas.';
            return;
        }
        points = type === 'Point' ? [point] : [...points, point];
        if (type === 'Polygon') closed = false;
        touched = true;
        sync();
    }
    listen(root.querySelector('[data-map-add]'), 'click', () => add(map.getCenter()));
    listen(root.querySelector('[data-map-finish]'), 'click', () => { closed = true; touched = true; sync('Poligon selesai. Periksa batas sebelum menyimpan.'); });
    listen(root.querySelector('[data-map-undo]'), 'click', () => { points.pop(); closed = false; touched = true; sync(); });
    listen(root.querySelector('[data-map-new]'), 'click', () => { points = []; closed = false; touched = true; sync('Klik peta untuk menggambar batas baru.'); });
    listen(root.querySelector('[data-map-clear]'), 'click', () => { points = []; closed = type === 'Point'; touched = true; sync('Lokasi dikosongkan. Perubahan belum tersimpan.'); });
    listen(root.querySelector('[data-map-move]'), 'click', () => {
        const index = Number(vertexSelect.value);
        if (points[index]) { points[index] = coordinate(map.getCenter()); touched = true; sync(); }
    });
    map.on('click', (event) => add(event.latlng));
    listen(form, 'submit', (event) => {
        if (!ready()) {
            event.preventDefault();
            status.textContent = 'Selesaikan poligon sebelum menyimpan.';
        }
    });
    sync();
    if (points.length) {
        map.fitBounds(L.latLngBounds(points.map((point) => [point[1], point[0]])), { padding: [45, 45], maxZoom: 15, animate: false });
    }
    listen(window, 'beforeunload', (event) => {
        if (touched && !form.dataset.mapSubmitting) { event.preventDefault(); event.returnValue = ''; }
    });
    listen(form, 'submit', () => { if (ready()) form.dataset.mapSubmitting = 'true'; });
}
