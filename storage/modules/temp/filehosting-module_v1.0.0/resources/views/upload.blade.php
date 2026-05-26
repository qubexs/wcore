@extends('layouts.admin')

@section('title', 'Upload Files')

@section('main-content' )
<div class="container-fluid mt-4" style="padding-top: 2rem;" >
    <div class="glass-panel p-4 mb-5" x-data="fileUpload()" x-init="loadFolders()" 
         style="backdrop-filter: blur(12px); background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 12px; padding-top: 3.5rem;">

        {{-- Header --}}
        <div class="d-flex align-items-center mb-4">
            <div class="me-3">
                <i class="fas fa-cloud-upload-alt fa-2x text-primary"></i>
            </div>
            <div>
                <h4 class="mb-0 fw-bold">Upload Files</h4>
                <p class="mb-0 text-muted small">Drag & drop your files or browse to upload</p>
            </div>
        </div>

        {{-- Dropzone --}}
        <div class="fh-dropzone"
             @dragover.prevent="isDragging = true"
             @dragleave="isDragging = false"
             @drop.prevent="handleDrop($event)"
             :class="{ 'fh-dropzone--active': isDragging }"
             style="border: 2px dashed #a5b4fc; border-radius: 12px; padding: 40px 20px; text-align:center; cursor:pointer; transition: all 0.3s ease; background: rgba(255,255,255,0.05);">

            <input type="file" id="fileInput" multiple class="fh-dropzone__input" @change="handleFiles($event.target.files)" style="display:none;">
            <div class="mb-3">
                <i class="fas fa-cloud-upload-alt dropzone-icon" style="font-size: 48px; color: #a5b4fc;"></i>
            </div>
            <p class="fw-bold">Drag & Drop files here, or</p>
            <button type="button" class="btn btn-primary btn-sm mt-2" @click="document.getElementById('fileInput').click()" style="border-radius: 20px;">Browse Files</button>
            <p class="text-muted small mt-2">Maximum file size: {{ ini_get('upload_max_filesize') }}</p>
        </div>

        {{-- Upload Options --}}
        <div class="mt-4">
            <div class="mb-3">
                <label class="form-label">Destination Folder</label>
                <select x-model="folderId" class="form-select">
                    <option value="">— Root (no folder) —</option>
                    <template x-for="f in folders" :key="f.id">
                        <option :value="f.id" x-text="f.path || f.name"></option>
                    </template>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Visibility</label>
                <select x-model="visibility" class="form-select">
                    <option value="private">Private</option>
                    <option value="public">Public</option>
                    <option value="restricted">Restricted</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Description (optional)</label>
                <textarea x-model="description" rows="3" class="form-control" placeholder="Brief description for all uploaded files…"></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Expires At (optional)</label>
                <input type="datetime-local" x-model="expiresAt" class="form-control">
            </div>
        </div>

        {{-- File Queue --}}
        <div class="mt-4" x-show="queue.length > 0">
            <div class="d-flex align-items-center mb-2">
                <h5 class="fw-bold mb-0">Queue (<span x-text="queue.length"></span> files)</h5>
                <button @click="uploadAll()" :disabled="uploading" class="btn btn-primary btn-sm ms-auto">
                    <span x-show="!uploading">Upload All</span>
                    <span x-show="uploading">Uploading…</span>
                </button>
            </div>

            <ul class="list-group">
                <template x-for="(item,index) in queue" :key="index">
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <span x-text="item.file.name" class="fw-bold"></span>
                            <span x-text="formatBytes(item.file.size)" class="text-muted small ms-2"></span>
                        </div>
                        <div class="d-flex align-items-center">
                            <template x-if="item.status==='pending'">
                                <span class="badge bg-secondary">Pending</span>
                            </template>
                            <template x-if="item.status==='uploading'">
                                <div class="progress" style="width: 100px; height: 6px; margin-right:10px;">
                                    <div class="progress-bar" :style="`width:${item.progress}%`"></div>
                                </div>
                            </template>
                            <template x-if="item.status==='done'">
                                <span class="badge bg-success">✓ Done</span>
                            </template>
                            <template x-if="item.status==='error'">
                                <span class="badge bg-danger" :title="item.error">✗ Error</span>
                            </template>
                            <button @click="removeFromQueue(index)" x-show="item.status==='pending'" class="btn btn-sm btn-outline-danger ms-2">&times;</button>
                        </div>
                    </li>
                </template>
            </ul>
        </div>

        {{-- Completed Summary --}}
        <div class="mt-3" x-show="doneCount>0 && !uploading">
            <p><strong x-text="doneCount"></strong> file(s) uploaded successfully.</p>
            <a href="{{ route('filehosting.index') }}" class="btn btn-success">Go to File Manager</a>
        </div>

    </div>
</div>
@endsection

@push('styles')
<style>
/* Glass Panel + Dropzone */
.glass-panel {
    backdrop-filter: blur(12px);
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 12px;
    transition: all 0.3s ease;
}

.fh-dropzone:hover {
    background: rgba(255,255,255,0.08);
    border-color: #818cf8;
}

.dropzone-icon {
    transition: transform 0.3s ease;
}

.fh-dropzone:hover .dropzone-icon {
    transform: scale(1.1);
}
</style>
@endpush

@push('scripts')
<script>
function fileUpload() {
    return {
        queue: [], folders: [], folderId: '', visibility: 'private', description: '', expiresAt: '',
        isDragging: false, uploading: false, doneCount: 0,

        async loadFolders() {
            const res = await fetch('{{ route('filehosting.folders.tree') }}', { headers:{'Accept':'application/json'} });
            const data = await res.json();
            this.folders = this.flattenTree(data);
        },

        flattenTree(nodes, prefix='') {
            let flat = [];
            for(const n of nodes){
                flat.push({id:n.id,name:n.name,path:prefix+n.name});
                if(n.children?.length) flat = flat.concat(this.flattenTree(n.children,prefix+n.name+' / '));
            }
            return flat;
        },

        handleFiles(fileList) { for(const f of fileList) this.queue.push({file:f,status:'pending',progress:0,error:''}); },
        handleDrop(event){ this.isDragging=false; this.handleFiles(event.dataTransfer.files); },
        removeFromQueue(index){ this.queue.splice(index,1); },

        async uploadAll(){
            this.uploading=true;
            for(const item of this.queue){
                if(item.status!=='pending') continue;
                await this.uploadItem(item);
            }
            this.uploading=false;
            this.doneCount=this.queue.filter(i=>i.status==='done').length;
        },

        async uploadItem(item){
            item.status='uploading';
            const fd=new FormData();
            fd.append('file',item.file);
            if(this.folderId) fd.append('folder_id',this.folderId);
            if(this.description) fd.append('description',this.description);
            if(this.expiresAt) fd.append('expires_at',this.expiresAt);
            fd.append('visibility',this.visibility);
            fd.append('_token',document.querySelector('meta[name=csrf-token]').content);

            return new Promise(resolve=>{
                const xhr=new XMLHttpRequest();
                xhr.open('POST','{{ route('filehosting.files.store') }}');
                xhr.setRequestHeader('Accept','application/json');

                xhr.upload.onprogress=e=>{ if(e.lengthComputable)item.progress=Math.round((e.loaded/e.total)*100); };
                xhr.onload=()=>{
                    if(xhr.status===201){ item.status='done'; item.progress=100; }
                    else{ item.status='error'; try{ item.error=JSON.parse(xhr.responseText).message||'Upload failed'; } catch{ item.error='Upload failed'; } }
                    resolve();
                };
                xhr.onerror=()=>{ item.status='error'; item.error='Network error'; resolve(); };
                xhr.send(fd);
            });
        },

        formatBytes(bytes){
            if(bytes<1024) return bytes+' B';
            if(bytes<1048576) return (bytes/1024).toFixed(1)+' KB';
            return (bytes/1048576).toFixed(1)+' MB';
        },
    };
}
</script>
@endpush