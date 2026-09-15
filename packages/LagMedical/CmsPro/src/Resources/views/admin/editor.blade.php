<x-admin::layouts>
    <x-slot:title>CMS Pro: {{ $page->page_title }}</x-slot>

    <v-cms-pro-editor></v-cms-pro-editor>

    @pushOnce('styles')
        <style>
            .cms-pro-scrollbar::-webkit-scrollbar { width: 6px; height: 6px; }
            .cms-pro-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 999px; }
            .cms-pro-canvas { background-color: #e2e8f0; background-image: radial-gradient(#94a3b8 0.6px, transparent 0.6px); background-size: 14px 14px; }
             .cms-pro-editor-shell { background: #f1f5f9; display: flex; flex-direction: column; inset: 0; position: fixed; z-index: 10002; }
            .cms-pro-editor-header { align-items: center; background: #fff; border-bottom: 1px solid #e2e8f0; display: flex; flex: 0 0 64px; justify-content: space-between; min-width: 980px; padding: 0 20px; }
            .cms-pro-editor-body { display: flex; flex: 1; min-height: 0; min-width: 980px; }
            .cms-pro-library { background: #fff; border-right: 1px solid #e2e8f0; flex: 0 0 288px; overflow-y: auto; padding: 16px; }
            .cms-pro-editor-main { flex: 1; min-width: 0; overflow: auto; padding: 32px; }
            .cms-pro-editor-canvas { background: #fff; min-height: 100%; overflow: hidden; }
            .cms-pro-settings { background: #fff; border-left: 1px solid #e2e8f0; flex: 0 0 320px; overflow-y: auto; padding: 16px; }
            @media (max-width: 1100px) {
                .cms-pro-editor-header, .cms-pro-editor-body { min-width: 900px; }
                .cms-pro-library { flex-basis: 240px; }
                .cms-pro-settings { flex-basis: 280px; }
                .cms-pro-editor-main { padding: 20px; }
            }
        </style>
    @endPushOnce

    @pushOnce('scripts')
        <script type="text/x-template" id="v-cms-pro-editor-template">
            <div class="cms-pro-editor-shell">
                <form
                    ref="saveForm"
                    method="POST"
                    :action="saveAction"
                    class="hidden"
                >
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="locale" value="{{ $locale }}">
                    <input type="hidden" name="status" :value="saveStatus">
                    <input type="hidden" name="blocks" :value="JSON.stringify(blocks)">
                    <input type="hidden" name="custom_css" :value="customCss">
                    <input type="hidden" name="custom_js" :value="customJs">
                </form>

                <header class="cms-pro-editor-header dark:border-gray-800 dark:bg-gray-900">
                    <div class="flex min-w-0 items-center gap-4">
                         <button type="button" class="text-2xl text-slate-500" title="Volver" @click="goBack">←</button>
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <span class="rounded bg-blue-600 px-2 py-1 text-[10px] font-bold uppercase tracking-wider text-white">CMS Pro</span>
                                <h1 class="truncate text-base font-semibold text-slate-900 dark:text-white">{{ $page->page_title }}</h1>
                            </div>
                             <p class="text-xs text-slate-500">{{ strtoupper($locale) }} · @{{ blocks.length }} bloques · Catálogo mayorista · {{ $content?->status === 'published' ? 'Publicado' : 'Borrador' }}</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <select v-model="selectedRevision" class="rounded-md border border-slate-300 px-2 py-2 text-xs dark:border-gray-700 dark:bg-gray-800">
                            <option value="">Revisiones</option>
                            <option v-for="revision in revisions" :value="revision.id">#@{{ revision.id }} · @{{ revision.status }} · @{{ revision.created_at }}</option>
                        </select>
                        <button v-if="selectedRevision" type="button" class="secondary-button" @click="restoreRevision">Restaurar</button>
                        <div class="mr-2 flex rounded-lg bg-slate-100 p-1 dark:bg-gray-800">
                            <button type="button" class="rounded px-3 py-1.5 text-xs" :class="device === 'desktop' ? 'bg-white shadow dark:bg-gray-700' : ''" title="Vista de escritorio" @click="device = 'desktop'">▣ <span class="sr-only">Escritorio</span></button>
                            <button type="button" class="rounded px-3 py-1.5 text-xs" :class="device === 'tablet' ? 'bg-white shadow dark:bg-gray-700' : ''" title="Vista de tableta" @click="device = 'tablet'">▤ <span class="sr-only">Tableta</span></button>
                            <button type="button" class="rounded px-3 py-1.5 text-xs" :class="device === 'mobile' ? 'bg-white shadow dark:bg-gray-700' : ''" title="Vista móvil" @click="device = 'mobile'">▯ <span class="sr-only">Móvil</span></button>
                        </div>
                         <button type="button" class="secondary-button" @click="openPreview">Vista previa</button>
                         <a href="{{ route('admin.cms.edit', ['id' => $page->id, 'locale' => $locale]) }}" class="secondary-button">SEO y ajustes</a>
                         <a href="{{ route('shop.cms.page', ['slug' => $page->url_key]) }}" target="_blank" rel="noopener" class="secondary-button">Abrir página</a>
                         <button type="button" class="secondary-button" @click="save('draft')">Guardar</button>
                         <span class="hidden text-xs text-slate-500 xl:inline">Se guardará como borrador</span>
                        @if (bouncer()->hasPermission('cms.pro.publish'))
                            <button type="button" class="primary-button" @click="save('published')">Publicar</button>
                        @endif
                    </div>
                </header>

                <div class="cms-pro-editor-body">
                    <aside class="cms-pro-library cms-pro-scrollbar dark:border-gray-800 dark:bg-gray-900">
                        <div class="mb-5">
                            <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-slate-500">Plantillas</label>
                            <select v-model="selectedTemplate" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800">
                                <option value="">Seleccionar plantilla</option>
                                <option v-for="(template, key) in templates" :value="key">@{{ template.name }}</option>
                            </select>
                            <button type="button" class="secondary-button mt-2 w-full" :disabled="! selectedTemplate" @click="applyTemplate">Aplicar plantilla</button>
                        </div>

                        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-slate-500">Bloques</label>
                        <div class="grid gap-2">
                            <button
                                v-for="(definition, type) in definitions"
                                :key="type"
                                type="button"
                                class="rounded-lg border border-slate-200 p-3 text-left transition hover:border-blue-500 hover:bg-blue-50 dark:border-gray-700 dark:hover:bg-gray-800"
                                @click="addBlock(type)"
                            >
                                <strong class="block text-sm text-slate-800 dark:text-white">@{{ definition.name }}</strong>
                                <span class="mt-1 block text-xs leading-4 text-slate-500">@{{ definition.description }}</span>
                            </button>
                        </div>
                    </aside>

                    <main class="cms-pro-canvas cms-pro-editor-main cms-pro-scrollbar">
                        <div
                            class="cms-pro-editor-canvas mx-auto shadow-xl transition-all dark:bg-gray-900"
                            :class="canvasClass"
                            :style="canvasStyle"
                        >
                            <div v-if="! blocks.length" class="grid min-h-[600px] place-items-center p-10 text-center">
                                <div>
                                    <p class="text-xl font-semibold text-slate-700 dark:text-white">Comienza con una plantilla o agrega un bloque</p>
                                    <p class="mt-2 text-sm text-slate-500">El contenido se adaptará automáticamente a escritorio y móvil.</p>
                                </div>
                            </div>

                            <draggable
                                v-else
                                v-model="blocks"
                                item-key="id"
                                handle=".cms-pro-drag"
                                ghost-class="opacity-30"
                                :animation="180"
                            >
                                <template #item="{ element: block, index }">
                                    <section
                                        class="group relative cursor-pointer border-2 transition"
                                        :class="selectedId === block.id ? 'border-blue-500' : 'border-transparent hover:border-blue-300'"
                                        :style="blockStyle(block)"
                                        @click.stop="selectBlock(block.id)"
                                    >
                                        <div class="absolute right-2 top-2 z-10 hidden items-center gap-1 rounded-md bg-slate-950 p-1 text-white shadow group-hover:flex" :class="selectedId === block.id ? '!flex' : ''">
                                            <button type="button" class="cms-pro-drag cursor-move px-2 py-1" title="Mover">↕</button>
                                            <button type="button" class="px-2 py-1" title="Subir" :disabled="index === 0" @click.stop="moveBlock(index, -1)">↑</button>
                                            <button type="button" class="px-2 py-1" title="Bajar" :disabled="index === blocks.length - 1" @click.stop="moveBlock(index, 1)">↓</button>
                                            <button type="button" class="px-2 py-1" title="Duplicar" @click.stop="duplicateBlock(index)">⧉</button>
                                            <button type="button" class="px-2 py-1 text-red-300" title="Eliminar" @click.stop="removeBlock(index)">×</button>
                                        </div>

                                        <div class="mx-auto" :class="containerClass(block)">
                                            <template v-if="block.type === 'hero'">
                                                <div class="relative grid min-h-[340px] place-items-center overflow-hidden bg-slate-900 px-8 text-center text-white">
                                                    <img v-if="mediaUrl(block.data.media_id || block.data.poster_id)" :src="mediaUrl(block.data.media_id || block.data.poster_id)" class="absolute inset-0 h-full w-full object-cover opacity-50">
                                                    <div class="relative max-w-3xl"><span class="text-xs font-bold uppercase tracking-[.2em]">@{{ block.data.eyebrow }}</span><h2 class="mt-3 text-4xl font-bold">@{{ block.data.title || 'Título del banner' }}</h2><p class="mt-4">@{{ plain(block.data.text) }}</p></div>
                                                </div>
                                            </template>
                                            <template v-else-if="block.type === 'media_text'">
                                                <div class="grid items-center gap-8 p-6 md:grid-cols-2"><img v-if="mediaUrl(block.data.media_id)" :src="mediaUrl(block.data.media_id)" class="aspect-[4/3] h-full w-full rounded-xl object-cover"><div><p class="text-xs font-bold uppercase text-blue-600">@{{ block.data.eyebrow }}</p><h2 class="mt-2 text-3xl font-bold">@{{ block.data.title || 'Imagen y texto' }}</h2><p class="mt-4 text-slate-600">@{{ plain(block.data.content) }}</p></div></div>
                                            </template>
                                            <template v-else-if="['feature_cards','gallery','testimonials','stats','logo_strip'].includes(block.type)">
                                                <div class="p-6"><h2 class="mb-6 text-3xl font-bold">@{{ block.data.title || definitions[block.type].name }}</h2><div class="grid gap-4" :class="device === 'mobile' ? 'grid-cols-1' : 'grid-cols-3'"><article v-for="item in block.data.items" class="rounded-xl border border-slate-200 p-5"><img v-if="mediaUrl(item.media_id)" :src="mediaUrl(item.media_id)" class="mb-4 aspect-video w-full rounded-lg object-cover"><strong>@{{ item.title || item.name || item.value }}</strong><p class="mt-2 text-sm text-slate-500">@{{ plain(item.text || item.quote || item.label || item.caption) }}</p></article><p v-if="! block.data.items?.length" class="text-sm text-slate-400">Agrega elementos desde el panel de propiedades.</p></div></div>
                                            </template>
                                            <template v-else-if="['product_grid','product_carousel'].includes(block.type)">
                                                <div class="p-6"><h2 class="mb-6 text-3xl font-bold">@{{ block.data.title || definitions[block.type].name }}</h2><div class="grid gap-4" :class="device === 'mobile' ? 'grid-cols-2' : 'grid-cols-4'"><div v-for="number in Math.min(Number(block.data.limit || 4), 8)" class="rounded-lg border border-slate-200 p-3"><div class="aspect-square rounded bg-slate-100"></div><div class="mt-3 h-3 rounded bg-slate-200"></div><div class="mt-2 h-3 w-2/3 rounded bg-slate-100"></div></div></div></div>
                                            </template>
                                             <template v-else-if="block.type === 'accordion'">
                                                 <div class="p-6"><h2 class="mb-6 text-3xl font-bold">@{{ block.data.title || 'Preguntas frecuentes' }}</h2><div v-for="item in block.data.items" class="border-t border-slate-200 py-4"><strong>@{{ item.title }}</strong><p class="mt-2 text-sm text-slate-500">@{{ plain(item.content) }}</p></div></div>
                                             </template>
                                             <template v-else-if="block.type === 'custom_html'">
                                                 <div class="border border-dashed border-blue-300 bg-blue-50 p-6 text-sm text-slate-600"><strong>HTML personalizado</strong><pre class="mt-3 max-h-48 overflow-auto whitespace-pre-wrap font-mono text-xs">@{{ block.data.html || 'Escribe el HTML desde el panel de propiedades.' }}</pre></div>
                                             </template>
                                            <template v-else>
                                                <div class="p-8 text-center"><span class="text-xs font-bold uppercase tracking-widest text-blue-600">@{{ definitions[block.type].name }}</span><h2 class="mt-3 text-3xl font-bold">@{{ block.data.title || definitions[block.type].name }}</h2><p class="mx-auto mt-4 max-w-2xl text-slate-500">@{{ plain(block.data.content || block.data.text || block.data.caption) }}</p></div>
                                            </template>
                                        </div>
                                    </section>
                                </template>
                            </draggable>
                        </div>
                    </main>

                    <aside class="cms-pro-settings cms-pro-scrollbar dark:border-gray-800 dark:bg-gray-900">
                        <template v-if="selectedBlock">
                            <div class="mb-4 flex items-center justify-between">
                                <div><p class="text-xs font-semibold uppercase tracking-wide text-blue-600">Propiedades</p><h2 class="font-semibold text-slate-900 dark:text-white">@{{ definitions[selectedBlock.type].name }}</h2></div>
                                <button type="button" class="text-2xl text-slate-400" @click="selectedId = null">×</button>
                            </div>

                            <div class="grid gap-4">
                                <template v-for="field in definitions[selectedBlock.type].fields" :key="field.key">
                                    <div v-if="isFieldVisible(field, selectedBlock.data)">
                                        <label class="mb-1.5 block text-xs font-semibold text-slate-600 dark:text-gray-300">@{{ field.label }}</label>
                                        <input v-if="['text','number','url'].includes(field.type)" :type="field.type" v-model="selectedBlock.data[field.key]" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800">
                                         <textarea v-else-if="['textarea','code'].includes(field.type)" v-model="selectedBlock.data[field.key]" :rows="field.type === 'code' ? 12 : 4" :class="field.type === 'code' ? 'font-mono text-xs' : ''" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800"></textarea>
                                        <select v-else-if="field.type === 'select'" v-model="selectedBlock.data[field.key]" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800"><option v-for="(label, value) in field.options" :value="value">@{{ label }}</option></select>
                                        <label v-else-if="field.type === 'switch'" class="flex items-center gap-2"><input type="checkbox" v-model="selectedBlock.data[field.key]"><span class="text-sm">Activado</span></label>
                                        <div v-else-if="field.type === 'media'" class="flex items-center gap-2"><img v-if="mediaUrl(selectedBlock.data[field.key])" :src="mediaUrl(selectedBlock.data[field.key])" class="h-14 w-14 rounded object-cover"><button type="button" class="secondary-button" @click="chooseMedia(selectedBlock.data, field.key)">Seleccionar</button><button v-if="selectedBlock.data[field.key]" type="button" class="text-sm text-red-600" @click="selectedBlock.data[field.key] = 0">Quitar</button></div>
                                        <div v-else-if="field.type === 'repeater'" class="grid gap-3">
                                            <div v-for="(item, itemIndex) in selectedBlock.data[field.key]" class="rounded-lg border border-slate-200 p-3 dark:border-gray-700">
                                                <div class="mb-2 flex justify-between"><strong class="text-xs">Elemento @{{ itemIndex + 1 }}</strong><button type="button" class="text-red-600" @click="selectedBlock.data[field.key].splice(itemIndex, 1)">Eliminar</button></div>
                                                <div class="grid gap-2"><template v-for="itemField in field.fields"><input v-if="['text','number','url'].includes(itemField.type)" :type="itemField.type" v-model="item[itemField.key]" :placeholder="itemField.label" class="w-full rounded border border-slate-300 px-2 py-1.5 text-xs dark:border-gray-700 dark:bg-gray-800"><textarea v-else-if="itemField.type === 'textarea'" v-model="item[itemField.key]" :placeholder="itemField.label" class="w-full rounded border border-slate-300 px-2 py-1.5 text-xs dark:border-gray-700 dark:bg-gray-800"></textarea><button v-else-if="itemField.type === 'media'" type="button" class="secondary-button text-xs" @click="chooseMedia(item, itemField.key)">@{{ mediaName(item[itemField.key]) || itemField.label }}</button></template></div>
                                            </div>
                                            <button type="button" class="secondary-button w-full" @click="addRepeaterItem(selectedBlock.data, field)">Agregar elemento</button>
                                        </div>
                                    </div>
                         </template>
                         @if ($canUseCustomCode)
                             <div class="border-t border-slate-200 pt-5 dark:border-gray-700">
                                 <p class="mb-1 text-xs font-semibold uppercase tracking-wide text-slate-500">Código de la página</p>
                                 <p class="mb-3 text-xs text-slate-500">CSS y JavaScript se aplican a toda esta página.</p>
                                 <label class="mb-3 block text-xs">CSS personalizado<textarea v-model="customCss" rows="8" class="mt-1 w-full rounded border border-slate-300 px-2 py-2 font-mono text-xs dark:border-gray-700 dark:bg-gray-800"></textarea></label>
                                 <label class="block text-xs">JavaScript personalizado<textarea v-model="customJs" rows="8" class="mt-1 w-full rounded border border-slate-300 px-2 py-2 font-mono text-xs dark:border-gray-700 dark:bg-gray-800"></textarea></label>
                             </div>
                         @endif
                            </div>

                            <div class="mt-6 border-t border-slate-200 pt-5 dark:border-gray-700">
                                 <p class="mb-4 text-xs font-semibold uppercase tracking-wide text-slate-500">Diseño responsive</p>
                                <div class="grid gap-3">
                                    <label class="text-xs">Contenedor<select v-model="selectedBlock.settings.container" class="mt-1 w-full rounded border border-slate-300 px-2 py-2 dark:border-gray-700 dark:bg-gray-800"><option value="full">Ancho completo</option><option value="wide">Amplio</option><option value="content">Contenido</option></select></label>
                                    <label class="text-xs">Fondo<input type="color" v-model="selectedBlock.settings.background" class="mt-1 h-9 w-full"></label>
                                    <label class="text-xs">Color de texto<input type="color" v-model="selectedBlock.settings.text_color" class="mt-1 h-9 w-full"></label>
                                    <label class="text-xs">Espacio superior<input type="range" min="0" max="200" v-model="selectedBlock.settings.padding_top" class="w-full"><span>@{{ selectedBlock.settings.padding_top }}px</span></label>
                                    <label class="text-xs">Espacio inferior<input type="range" min="0" max="200" v-model="selectedBlock.settings.padding_bottom" class="w-full"><span>@{{ selectedBlock.settings.padding_bottom }}px</span></label>
                                    <label class="text-xs">Espacio móvil<input type="range" min="0" max="120" v-model="selectedBlock.settings.mobile_padding" class="w-full"><span>@{{ selectedBlock.settings.mobile_padding }}px</span></label>
                                    <label class="text-xs">Ancla HTML<input type="text" v-model="selectedBlock.settings.anchor" class="mt-1 w-full rounded border border-slate-300 px-2 py-2 dark:border-gray-700 dark:bg-gray-800"></label>
                                    <label class="flex items-center gap-2 text-xs"><input type="checkbox" v-model="selectedBlock.settings.hide_desktop"> Ocultar en escritorio</label>
                                    <label class="flex items-center gap-2 text-xs"><input type="checkbox" v-model="selectedBlock.settings.hide_mobile"> Ocultar en móvil</label>
                                </div>
                            </div>
                        </template>
                        <div v-else class="grid h-full place-items-center text-center text-sm text-slate-400">Selecciona un bloque para editar sus propiedades.</div>
                    </aside>
                </div>

                <div v-if="mediaOpen" class="fixed inset-0 z-[1100] grid place-items-center bg-slate-950/70 p-6" @click.self="mediaOpen = false">
                    <div class="flex max-h-[85vh] w-full max-w-5xl flex-col rounded-xl bg-white shadow-2xl dark:bg-gray-900">
                        <div class="flex items-center justify-between border-b border-slate-200 p-5 dark:border-gray-700"><div><h2 class="text-lg font-semibold">Biblioteca de imágenes</h2><p class="text-xs text-slate-500">JPG, PNG, WEBP o GIF. Máximo 10 MB.</p></div><button type="button" class="text-2xl" @click="mediaOpen = false">×</button></div>
                        <div class="flex flex-wrap gap-3 border-b border-slate-200 p-4 dark:border-gray-700">
                            <label class="primary-button cursor-pointer">Subir imágenes<input type="file" accept="image/jpeg,image/png,image/webp,image/gif" multiple class="hidden" @change="uploadMedia"></label>
                            <input v-model="mediaAlt" type="text" placeholder="Texto alternativo" class="min-w-56 rounded border border-slate-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800">
                            <input v-model="importUrl" type="url" placeholder="https://.../imagen.jpg" class="min-w-72 flex-1 rounded border border-slate-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800">
                            <button type="button" class="secondary-button" :disabled="! importUrl" @click="importMedia">Importar URL</button>
                        </div>
                        <div class="cms-pro-scrollbar grid flex-1 grid-cols-3 gap-3 overflow-y-auto p-4 md:grid-cols-5">
                            <div v-for="item in media" class="group relative overflow-hidden rounded-lg border border-slate-200 text-left hover:border-blue-500 dark:border-gray-700"><button type="button" class="w-full text-left" @click="selectMedia(item)"><img :src="item.url" :alt="item.alt_text || item.name" class="aspect-square w-full object-cover"><span class="block truncate p-2 text-xs">@{{ item.name }}</span></button>@if (bouncer()->hasPermission('cms.pro.media.delete'))<button type="button" class="absolute right-1 top-1 hidden rounded bg-white/90 px-2 py-1 text-xs text-red-600 shadow group-hover:block" @click="deleteMedia(item)">Eliminar</button>@endif</div>
                        </div>
                    </div>
                </div>

                <div v-if="previewOpen" class="fixed inset-0 z-[1200] flex flex-col bg-slate-100 dark:bg-gray-950">
                    <header class="flex h-16 items-center justify-between border-b bg-white px-5 dark:border-gray-800 dark:bg-gray-900"><strong>Vista previa</strong><button type="button" class="secondary-button" @click="previewOpen = false">Cerrar</button></header>
                    <div class="cms-pro-scrollbar flex-1 overflow-auto p-6"><div class="mx-auto min-h-full max-w-[1440px] bg-white shadow-xl" v-html="previewHtml"></div></div>
                </div>
            </div>
        </script>

        <script type="module">
            app.component('v-cms-pro-editor', {
                template: '#v-cms-pro-editor-template',

                data() {
                    return {
                        definitions: @json($definitions),
                        templates: @json($templates),
                        revisions: @json($revisions),
                        blocks: @json($content?->blocks ?? []),
                        customCss: @json($content?->custom_css ?? ''),
                        customJs: @json($content?->custom_js ?? ''),
                        media: @json($media),
                        channelId: @json($channelId),
                        selectedId: null,
                        selectedTemplate: '',
                        selectedRevision: '',
                        device: 'desktop',
                        saveStatus: 'draft',
                        saveAction: @json(route('admin.cms.pro.update', $page->id)),
                        draftAction: @json(route('admin.cms.pro.update', $page->id)),
                        publishAction: @json(route('admin.cms.pro.publish', $page->id)),
                        mediaOpen: false,
                        mediaTarget: null,
                        importUrl: '',
                        mediaAlt: '',
                        previewOpen: false,
                        previewHtml: '',
                    };
                },

                computed: {
                    selectedBlock() {
                        return this.blocks.find(block => block.id === this.selectedId);
                    },

                    canvasClass() {
                        return '';
                    },

                    canvasStyle() {
                        return {
                            width: '100%',
                            maxWidth: { desktop: '1440px', tablet: '820px', mobile: '390px' }[this.device],
                        };
                    },
                },

                methods: {
                     clone(value) {
                         try {
                             return JSON.parse(JSON.stringify(value));
                         } catch (error) {
                             console.error('CMS Pro: no se pudo clonar el contenido.', error);
                             return {};
                         }
                     },
                     uuid() {
                         return window.crypto?.randomUUID?.() || `${Date.now()}-${Math.random().toString(16).slice(2)}`;
                    },

                    defaults(type) {
                        const data = {};
                         this.definitions[type].fields.forEach(field => data[field.key] = this.clone(field.default ?? (field.type === 'repeater' ? [] : '')));
                        return { id: this.uuid(), type, data, settings: { container: 'wide', background: '#ffffff', text_color: '#0f172a', padding_top: 64, padding_bottom: 64, mobile_padding: 32, hide_desktop: false, hide_mobile: false, anchor: '' } };
                    },

                    addBlock(type) {
                        const block = this.defaults(type);
                        this.blocks.push(block);
                        this.selectedId = block.id;
                    },

                     applyTemplate() {
                         const template = this.templates[this.selectedTemplate];
                         if (! template || ! Array.isArray(template.blocks) || ! template.blocks.length) {
                             alert('La plantilla seleccionada no contiene bloques.');
                             return;
                         }
                         if (this.blocks.length && ! confirm('Esto reemplazará el contenido actual. ¿Continuar?')) return;
                         this.blocks = this.clone(template.blocks).map(block => ({ ...block, id: this.uuid() }));
                         this.selectedId = this.blocks[0]?.id ?? null;
                     },

                    selectBlock(id) { this.selectedId = id; },
                    removeBlock(index) { if (confirm('¿Eliminar este bloque?')) { this.blocks.splice(index, 1); this.selectedId = null; } },
                     duplicateBlock(index) { const block = this.clone(this.blocks[index]); block.id = this.uuid(); this.blocks.splice(index + 1, 0, block); this.selectedId = block.id; },
                     moveBlock(index, direction) {
                         const target = index + direction;
                         if (target < 0 || target >= this.blocks.length) return;
                         const [block] = this.blocks.splice(index, 1);
                         this.blocks.splice(target, 0, block);
                     },
                     plain(value) { const element = document.createElement('div'); element.innerHTML = value || ''; return element.textContent || ''; },
                     goBack() { window.history.length > 1 ? window.history.back() : window.location.href = @json(route('admin.cms.edit', ['id' => $page->id, 'locale' => $locale])); },
                    isFieldVisible(field, data) { return ! Object.keys(field.show_when || {}).length || Object.entries(field.show_when).every(([key, value]) => data[key] === value); },
                     addRepeaterItem(data, field) { const item = {}; field.fields.forEach(itemField => item[itemField.key] = this.clone(itemField.default ?? (itemField.type === 'media' ? 0 : ''))); data[field.key].push(item); },
                    mediaUrl(id) { return this.media.find(item => Number(item.id) === Number(id))?.url || ''; },
                    mediaName(id) { return this.media.find(item => Number(item.id) === Number(id))?.name || ''; },
                    chooseMedia(target, key) { this.mediaTarget = { target, key }; this.mediaOpen = true; },
                    selectMedia(item) { this.mediaTarget.target[this.mediaTarget.key] = item.id; this.mediaOpen = false; },

                    async deleteMedia(item) {
                        if (! confirm(`¿Eliminar ${item.name}?`)) return;
                        try {
                            await this.$axios.delete(@json(route('admin.cms.pro.media.delete', ['media' => '__ID__'])).replace('__ID__', item.id), { params: { channel_id: this.channelId } });
                            this.media = this.media.filter(media => media.id !== item.id);
                        } catch (error) { alert(error.response?.data?.message || 'No se pudo eliminar la imagen.'); }
                    },

                    containerClass(block) {
                        if (block.settings.container === 'content') return 'max-w-4xl';
                        if (block.settings.container === 'wide') return 'max-w-7xl';
                        return 'max-w-none';
                    },

                    blockStyle(block) {
                        const mobile = this.device === 'mobile';
                        return {
                            backgroundColor: block.settings.background || '#ffffff',
                            color: block.settings.text_color || '#0f172a',
                            paddingTop: `${mobile ? block.settings.mobile_padding : block.settings.padding_top}px`,
                            paddingBottom: `${mobile ? block.settings.mobile_padding : block.settings.padding_bottom}px`,
                            display: (mobile && block.settings.hide_mobile) || (! mobile && block.settings.hide_desktop) ? 'none' : '',
                        };
                    },

                    async uploadMedia(event) {
                        const data = new FormData();
                        Array.from(event.target.files).forEach(file => data.append('files[]', file));
                        if (this.channelId) data.append('channel_id', this.channelId);
                        if (this.mediaAlt) data.append('alt_text', this.mediaAlt);
                        try { const response = await this.$axios.post(@json(route('admin.cms.pro.media.store')), data); this.media.unshift(...response.data.data); }
                        catch (error) { alert(error.response?.data?.message || 'No se pudieron subir las imágenes.'); }
                        event.target.value = '';
                    },

                    async importMedia() {
                        try {
                            const response = await this.$axios.post(@json(route('admin.cms.pro.media.import')), { url: this.importUrl, channel_id: this.channelId, alt_text: this.mediaAlt });
                            this.media.unshift(...response.data.data); this.importUrl = '';
                        } catch (error) { alert(error.response?.data?.message || 'No se pudo importar la imagen.'); }
                    },

                    async openPreview() {
                        try {
                            const response = await this.$axios.post(@json(route('admin.cms.pro.preview', $page->id)), { blocks: this.blocks, custom_css: this.customCss, custom_js: this.customJs });
                            this.previewHtml = response.data.html; this.previewOpen = true;
                        } catch (error) { alert(error.response?.data?.message || 'No se pudo generar la vista previa.'); }
                    },

                    async restoreRevision() {
                        if (! confirm('La revisión seleccionada reemplazará el borrador actual. ¿Continuar?')) return;
                        try {
                            await this.$axios.post(@json(route('admin.cms.pro.revisions.restore', ['pageId' => $page->id, 'revision' => '__ID__'])).replace('__ID__', this.selectedRevision), { locale: @json($locale) });
                            window.location.reload();
                        } catch (error) { alert(error.response?.data?.message || 'No se pudo restaurar la revisión.'); }
                    },

                    save(status) {
                        this.saveStatus = status;
                        this.saveAction = status === 'published' ? this.publishAction : this.draftAction;
                        this.$nextTick(() => this.$refs.saveForm.submit());
                    },
                },
            });
        </script>
    @endPushOnce
</x-admin::layouts>
