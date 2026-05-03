<script setup>
import InputError from '@/Components/InputError.vue';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { useForm, usePage } from '@inertiajs/vue3';
import { computed, nextTick, reactive, ref, watch } from 'vue';

const page = usePage();
const user = computed(() => page.props.auth.user);
const currentPhotoUrl = computed(() => user.value?.profile_photo_url || null);

const canvas = ref(null);
const image = ref(null);
const previewReady = ref(false);
const showCropModal = ref(false);
const fileInput = ref(null);
const zoom = ref(1);
const offset = reactive({ x: 0, y: 0 });
const drag = reactive({ active: false, x: 0, y: 0 });

const canvasSize = 280;
const outputSize = 512;
const maxFileSize = 2 * 1024 * 1024;
const form = useForm({ photo: null });

const initials = computed(() => (user.value?.name || 'U')
    .split(' ')
    .map((part) => part[0])
    .join('')
    .slice(0, 2)
    .toUpperCase());

const draw = () => {
    const context = canvas.value?.getContext('2d');

    if (!context || !image.value) {
        return;
    }

    const img = image.value;
    const baseScale = Math.max(canvasSize / img.naturalWidth, canvasSize / img.naturalHeight);
    const scale = baseScale * Number(zoom.value);
    const width = img.naturalWidth * scale;
    const height = img.naturalHeight * scale;

    constrainOffset(width, height);

    context.clearRect(0, 0, canvasSize, canvasSize);
    context.fillStyle = '#f3f4f6';
    context.fillRect(0, 0, canvasSize, canvasSize);
    context.drawImage(
        img,
        (canvasSize - width) / 2 + offset.x,
        (canvasSize - height) / 2 + offset.y,
        width,
        height,
    );
};

const constrainOffset = (width = null, height = null) => {
    if (!image.value) {
        return;
    }

    const img = image.value;
    const scale = Math.max(canvasSize / img.naturalWidth, canvasSize / img.naturalHeight) * Number(zoom.value);
    const renderedWidth = width ?? img.naturalWidth * scale;
    const renderedHeight = height ?? img.naturalHeight * scale;
    const maxX = Math.max(0, (renderedWidth - canvasSize) / 2);
    const maxY = Math.max(0, (renderedHeight - canvasSize) / 2);

    offset.x = Math.min(maxX, Math.max(-maxX, offset.x));
    offset.y = Math.min(maxY, Math.max(-maxY, offset.y));
};

const loadFile = (event) => {
    const file = event.target.files?.[0] || null;
    form.clearErrors('photo');

    if (!file) {
        return;
    }

    if (!file.type.startsWith('image/')) {
        form.setError('photo', 'File harus berupa gambar.');
        event.target.value = '';

        return;
    }

    if (file.size > maxFileSize) {
        form.setError('photo', 'Ukuran file maksimal 2 MB.');
        event.target.value = '';

        return;
    }

    const reader = new FileReader();
    reader.onload = async () => {
        const img = new Image();
        img.onload = async () => {
            image.value = img;
            zoom.value = 1;
            offset.x = 0;
            offset.y = 0;
            previewReady.value = true;
            showCropModal.value = true;
            await nextTick();
            draw();
        };
        img.src = reader.result;
    };
    reader.readAsDataURL(file);
};

const startDrag = (event) => {
    if (!previewReady.value) {
        return;
    }

    drag.active = true;
    drag.x = event.clientX ?? event.touches?.[0]?.clientX ?? 0;
    drag.y = event.clientY ?? event.touches?.[0]?.clientY ?? 0;
};

const moveDrag = (event) => {
    if (!drag.active) {
        return;
    }

    const currentX = event.clientX ?? event.touches?.[0]?.clientX ?? drag.x;
    const currentY = event.clientY ?? event.touches?.[0]?.clientY ?? drag.y;

    offset.x += currentX - drag.x;
    offset.y += currentY - drag.y;
    drag.x = currentX;
    drag.y = currentY;
    draw();
};

const stopDrag = () => {
    drag.active = false;
};

const closeCropModal = () => {
    showCropModal.value = false;
    previewReady.value = false;
    image.value = null;
    form.clearErrors('photo');

    if (fileInput.value) {
        fileInput.value.value = '';
    }
};

const save = () => {
    if (!previewReady.value || !canvas.value) {
        form.setError('photo', 'Pilih foto terlebih dahulu.');

        return;
    }

    const output = document.createElement('canvas');
    output.width = outputSize;
    output.height = outputSize;
    output.getContext('2d').drawImage(canvas.value, 0, 0, outputSize, outputSize);

    output.toBlob((blob) => {
        if (!blob) {
            form.setError('photo', 'Foto gagal diproses.');

            return;
        }

        if (blob.size > maxFileSize) {
            form.setError('photo', 'Ukuran hasil foto maksimal 2 MB.');

            return;
        }

        form.photo = new File([blob], 'profile-photo.jpg', { type: 'image/jpeg' });
        form.post(route('profile.photo.update'), {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => {
                closeCropModal();
                form.reset('photo');
            },
        });
    }, 'image/jpeg', 0.9);
};

watch(zoom, draw);
watch(showCropModal, async (value) => {
    if (value) {
        await nextTick();
        draw();
    }
});
</script>

<template>
    <section class="photo-form">
        <div class="current-photo" aria-label="Foto profil saat ini">
            <img v-if="currentPhotoUrl" :src="currentPhotoUrl" :alt="user?.name || 'Foto profil'" />
            <span v-else>{{ initials }}</span>
        </div>

        <h2>Foto Profil</h2>
        <p>Pilih foto, atur posisi dan zoom, lalu simpan.</p>

        <label class="file-button">
            Pilih Foto
            <input ref="fileInput" type="file" accept="image/png,image/jpeg,image/jpg,image/webp" @change="loadFile" />
        </label>

        <InputError :message="form.errors.photo" class="mt-3" />

        <Modal :show="showCropModal" max-width="lg" @close="closeCropModal">
            <div class="crop-modal">
                <div class="crop-modal-head">
                    <div>
                        <h3>Atur Foto Profil</h3>
                        <p>Geser foto untuk mengatur posisi, lalu gunakan slider untuk zoom.</p>
                    </div>
                    <button type="button" class="close-button" aria-label="Tutup" @click="closeCropModal">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M6 6l12 12M18 6 6 18" />
                        </svg>
                    </button>
                </div>

                <div class="cropper">
                    <canvas
                        v-show="previewReady"
                        ref="canvas"
                        :width="canvasSize"
                        :height="canvasSize"
                        class="crop-canvas"
                        @mousedown="startDrag"
                        @mousemove="moveDrag"
                        @mouseup="stopDrag"
                        @mouseleave="stopDrag"
                        @touchstart.prevent="startDrag"
                        @touchmove.prevent="moveDrag"
                        @touchend="stopDrag"
                    />

                    <label class="zoom-control">
                        <span>Zoom</span>
                        <input v-model.number="zoom" type="range" min="1" max="3" step="0.05" />
                    </label>

                    <InputError :message="form.errors.photo" />
                </div>

                <div class="crop-actions">
                    <button type="button" class="cancel-button" @click="closeCropModal">
                        Batal
                    </button>
                    <PrimaryButton type="button" :disabled="form.processing" @click="save">
                        Simpan Foto
                    </PrimaryButton>
                </div>
            </div>
        </Modal>
    </section>
</template>

<style scoped>
.photo-form {
    display: grid;
    justify-items: center;
    gap: 14px;
}

.current-photo {
    display: grid;
    place-items: center;
    width: 92px;
    height: 92px;
    overflow: hidden;
    border-radius: 50%;
    background: linear-gradient(135deg, #dbeafe, #f8fafc);
    color: #1d4ed8;
    font-size: 28px;
    font-weight: 800;
    box-shadow: 0 10px 24px rgba(31, 45, 61, .12);
}

.current-photo img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.photo-form h2 {
    margin: 0;
    color: #2f3441;
    font-size: 18px;
    font-weight: 700;
}

.photo-form p {
    margin: 0;
    color: #8d9498;
    font-size: 14px;
    line-height: 1.5;
}

.file-button {
    position: relative;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 38px;
    padding: 0 14px;
    border-radius: 6px;
    background: #2563eb;
    color: #fff;
    font-size: 13px;
    font-weight: 700;
    cursor: pointer;
}

.file-button input {
    position: absolute;
    inset: 0;
    opacity: 0;
    cursor: pointer;
}

.cropper {
    display: grid;
    justify-items: stretch;
    gap: 16px;
    width: 100%;
}

.crop-modal {
    display: grid;
    gap: 18px;
    padding: 22px;
}

.crop-modal-head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 16px;
}

.crop-modal-head h3 {
    margin: 0 0 6px;
    color: #111827;
    font-size: 18px;
    font-weight: 800;
}

.crop-modal-head p {
    margin: 0;
    color: #6b7280;
    font-size: 14px;
    line-height: 1.5;
}

.close-button {
    display: inline-grid;
    place-items: center;
    width: 34px;
    height: 34px;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    color: #4b5563;
    background: #fff;
}

.close-button svg {
    width: 18px;
    height: 18px;
}

.close-button path {
    fill: none;
    stroke: currentColor;
    stroke-width: 2;
    stroke-linecap: round;
}

.crop-canvas {
    width: 100%;
    max-width: 280px;
    aspect-ratio: 1;
    justify-self: center;
    border-radius: 50%;
    border: 1px solid #d9e2ec;
    cursor: grab;
    touch-action: none;
}

.crop-canvas:active {
    cursor: grabbing;
}

.zoom-control {
    display: grid;
    gap: 6px;
    color: #5f6b7a;
    font-size: 13px;
    font-weight: 700;
    text-align: left;
}

.zoom-control input {
    width: 100%;
    accent-color: #2563eb;
}

.crop-actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    border-top: 1px solid #eef2f7;
    padding-top: 16px;
}

.cancel-button {
    min-height: 38px;
    padding: 0 14px;
    border-radius: 6px;
    background: #f3f4f6;
    color: #374151;
    font-size: 13px;
    font-weight: 700;
}

@media (max-width: 640px) {
    .crop-modal {
        padding: 18px;
    }

    .crop-actions {
        display: grid;
        grid-template-columns: 1fr;
    }
}
</style>
