{{-- resources/views/modules/jobs/editJob.blade.php --}}
@section('title','Edit Job')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
<style>
*{box-sizing:border-box}
body{
  background:
    radial-gradient(circle at top right, rgba(35,119,252,.08), transparent 18%),
    var(--bg-body);
  font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,'Inter',sans-serif
}

/* Page shell */
.page-head{margin-bottom:28px}
.page-indicator{
  display:inline-flex;align-items:center;gap:12px;background:var(--surface);
  border:1px solid var(--border-color);border-radius:var(--radius-md);
  padding:16px 20px;box-shadow:var(--shadow-sm);color:var(--text-color)
}
.page-indicator i{color:var(--primary-color);font-size:20px}
.page-indicator strong{font-size:20px;font-weight:700}
.page-indicator .small{font-size:14px;color:var(--muted-color)}

/* Card */
.card-like{
  background:linear-gradient(180deg,#ffffff,#fbfdff);border:1px solid var(--border-color);
  border-radius:22px;box-shadow:0 20px 40px rgba(15,23,42,.08);
  padding:26px;margin-bottom:20px
}

/* Forms */
.form-label{display:block;font-size:13px;font-weight:600;color:var(--muted-color);margin-bottom:8px}
.input-group-text{
  background:var(--light-color);border:1px solid var(--border-color);
  color:var(--muted-color);height:44px
}
.form-control,.form-select{
  width:100%;height:44px;padding:0 14px;border:1px solid var(--border-color);
  border-radius:var(--radius-sm);font-size:14px;color:var(--text-color);
  background:var(--surface);transition:all .2s
}
.form-control:focus,.form-select:focus{outline:none;border-color:var(--primary-color);box-shadow:0 0 0 3px rgba(59,130,246,.1)}

/* Switches */
.form-check-input:focus{box-shadow:0 0 0 .2rem var(--ring)}
.form-check-input{background-color:var(--surface);border:1px solid var(--border-color)}
.form-check-input:checked{background-color:var(--primary-color);border-color:var(--primary-color)}

/* Buttons */
.btn{display:inline-flex;align-items:center;gap:8px;height:44px;padding:0 20px;border-radius:var(--radius-sm);font-size:14px;font-weight:600;cursor:pointer;transition:all .2s;border:none;text-decoration:none;justify-content:center}
.btn-primary{background:linear-gradient(135deg,#3b82f6 0%,#2563eb 100%);color:#fff;box-shadow:0 2px 8px rgba(59,130,246,.25)}
.btn-primary:hover:not(:disabled){transform:translateY(-1px);box-shadow:0 4px 12px rgba(59,130,246,.35)}
.btn-secondary{background:var(--surface);color:var(--text-color);border:1px solid var(--border-color)}
.btn-secondary:hover:not(:disabled){background:var(--primary-color);border-color:var(--primary-color);color:#fff}
.btn-light{background:var(--light-color);color:var(--text-color);border:1px solid var(--border-color)}
.btn-light:hover:not(:disabled){background:var(--primary-color);border-color:var(--primary-color);color:#fff}
.btn-outline-secondary{background:transparent;color:var(--text-color);border:1px solid var(--border-color)}
.btn-outline-secondary:hover:not(:disabled){background:var(--primary-color);border-color:var(--primary-color);color:#fff}
.btn:disabled{opacity:.6;cursor:not-allowed;transform:none!important}

/* Stepper */
.stepper{display:flex;gap:12px;margin:0 0 28px;flex-wrap:wrap}
.step{flex:1;display:flex;align-items:center;gap:12px;padding:18px;border:1px solid var(--border-color);border-radius:18px;background:linear-gradient(180deg,#ffffff,#f8fbff);cursor:pointer;transition:all .2s;min-width:200px;box-shadow:var(--shadow-xs)}
.step:hover{box-shadow:var(--shadow-sm)}
.step .num{width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;border:1px solid var(--border-color);background:var(--surface);color:var(--text-color);font-size:14px}
.step.active{border-color:var(--primary-color)!important}
.step.active .num{background:var(--primary-color);color:#fff;border-color:var(--primary-color)}
.step.done{opacity:.9}
.step .label{font-weight:600;color:var(--text-color)}

/* Editor */
.editor-area{min-height:200px;border:1px solid var(--border-color);border-radius:var(--radius-sm);padding:16px;background:var(--surface);color:var(--text-color);line-height:1.6}
.toolbar-row{display:flex;flex-wrap:wrap;gap:8px;align-items:center;margin-bottom:12px}
.tool-btn{padding:8px 12px;border-radius:6px;border:1px solid var(--border-color);background:var(--surface);cursor:pointer;color:var(--text-color);transition:all .2s}
.tool-btn:hover:not(:disabled){background:var(--primary-color);border-color:var(--primary-color);color:#fff}
.toolbar-title{font-size:12px;font-weight:700;opacity:.7;margin-right:6px;color:var(--muted-color)}
.toolbar-divider{flex:1 1 auto}

/* Media grid (image modal → library) */
.grid.media{display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:16px}
.media-card{border:1px solid var(--border-color);border-radius:12px;background:var(--surface);overflow:hidden;transition:.15s ease box-shadow,.15s ease transform,.15s ease border-color}
.media-card:hover{box-shadow:var(--shadow-sm);transform:translateY(-2px)}
.media-card.selected{border-color:var(--primary-color);box-shadow:0 0 0 3px rgba(59,130,246,.15)}
.media-thumb{width:100%;aspect-ratio:4/3;object-fit:cover;background:var(--light-color)}
.media-meta{padding:10px 12px;font-size:12px;color:var(--text-color)}

/* Fancy upload tile */
.upload-tile{
  display:flex;align-items:center;justify-content:center;gap:12px;
  width:100%;min-height:140px;border:2px dashed var(--border-color);
  border-radius:20px;background:linear-gradient(180deg,#fbfdff,#f3f8ff);
  cursor:pointer; transition:border-color .2s, transform .2s, box-shadow .2s;
}
.upload-tile:hover{border-color:var(--primary-color);box-shadow:var(--shadow-sm);transform:translateY(-1px)}
.upload-tile .icon{font-size:28px;color:var(--primary-color)}
.upload-tile .text{display:flex;flex-direction:column;line-height:1.3}
.upload-tile .text b{color:var(--text-color)}
.upload-tile .text span{font-size:12px;color:var(--muted-color)}
.upload-preview{
  width:100%;border:1px dashed var(--border-color);border-radius:12px;overflow:hidden;background:var(--surface)
}
.upload-preview img{display:block;width:100%;max-height:260px;object-fit:contain;background:var(--light-color)}

/* ✅ Document upload dropzone (same as Create page) */
.dropzone{
  display:flex;align-items:center;justify-content:center;gap:12px;
  width:100%;min-height:140px;border:2px dashed var(--border-color);
  border-radius:20px;background:linear-gradient(180deg,#fbfdff,#f3f8ff);
  cursor:pointer; transition:border-color .2s, transform .2s, box-shadow .2s;
}
.dropzone:hover{border-color:var(--primary-color);box-shadow:var(--shadow-sm);transform:translateY(-1px)}
.dropzone.dragover{border-color:var(--primary-color);box-shadow:0 0 0 3px rgba(59,130,246,.12)}
.dropzone .icon{font-size:26px;color:var(--primary-color)}
.dropzone .text{display:flex;flex-direction:column;line-height:1.25}
.dropzone .text b{color:var(--text-color)}
.dropzone .text span{font-size:12px;color:var(--muted-color)}
.file-pill{
  display:flex;align-items:center;justify-content:space-between;gap:12px;
  padding:10px 12px;border:1px dashed var(--border-color);border-radius:12px;background:var(--surface)
}
.file-pill .meta{display:flex;flex-direction:column;gap:2px}
.file-pill .meta b{font-size:13px;color:var(--text-color)}
.file-pill .meta small{font-size:12px;color:var(--muted-color)}

/* Misc */
.badge-soft{font-size:.7rem;border:1px solid var(--border-color);border-radius:6px;padding:4px 8px;background:var(--light-color);color:var(--text-color)}
.spinner{width:18px;height:18px;border:3px solid #0001;border-top-color:var(--primary-color);border-radius:50%;animation:spin 1s linear infinite}
@keyframes spin{to{transform:rotate(360deg)}}
.overlay-dim{position:absolute;inset:0;background:rgba(0,0,0,.08);border-radius:var(--radius-md);display:none;align-items:center;justify-content:center;z-index:2}
.overlay-dim.show{display:flex}
.btn[aria-busy="true"]{position:relative;pointer-events:none;opacity:.9}
.btn[aria-busy="true"] .busy-label{opacity:0}
.btn[aria-busy="true"]::after{content:"";position:absolute;left:50%;width:16px;height:16px;margin-left:-8px;border-radius:50%;border:2px solid #fff8;border-top-color:#fff;animation:spin .9s linear infinite}
.field-error{color:#e11d48;font-size:12px;margin-top:6px;display:none}
.field-error:not(:empty){display:block}

/* Modal polish */
.modal-content{border-radius:var(--radius-md);border:none;box-shadow:0 20px 40px rgba(0,0,0,.15);background:var(--surface)}
.modal-header{padding:20px 24px;border-bottom:1px solid var(--border-color);background:var(--surface)}
.modal-title{font-size:18px;font-weight:700;color:var(--text-color)}
.modal-body{padding:24px}
.modal-footer{padding:20px 24px;border-top:1px solid var(--border-color);display:flex;justify-content:flex-end;gap:12px;background:var(--light-color)}

/* Parent tree — beautified */
.parent-tree{list-style:none;margin:0;padding:0 0 0 8px;position:relative}
.parent-tree::before{content:"";position:absolute;left:14px;top:0;bottom:8px;width:1px;background:var(--border-color)}
.parent-tree > li{position:relative;margin:0 0 8px 0;padding-left:24px}
.parent-tree > li::before{content:"";position:absolute;left:14px;top:16px;width:16px;height:1px;background:var(--border-color)}
.parent-item{display:flex;align-items:center;gap:10px;padding:10px 12px;border:1px solid var(--border-color);border-radius:12px;background:var(--surface);transition:box-shadow .2s,border-color .2s,transform .2s}
.parent-item:hover{border-color:var(--primary-color);box-shadow:var(--shadow-sm);transform:translateY(-1px)}
.parent-item .toggle{width:28px;height:28px;border:1px solid var(--border-color);border-radius:8px;background:var(--light-color);display:flex;align-items:center;justify-content:center;cursor:pointer;transition:transform .2s,border-color .2s}
.parent-item .toggle i{transition:transform .2s}
.parent-item .toggle.open i{transform:rotate(90deg)}
.parent-title{display:flex;align-items:center;gap:8px}
.parent-title .chip{font-size:11px;padding:2px 8px;border-radius:999px;background:var(--light-color);border:1px solid var(--border-color);color:var(--muted-color)}
.parent-children{margin:8px 0 10px 0;padding-left:24px;display:none}
.parent-children .parent-children{margin-left:16px}

/* Radio nicer */
.parent-item input[type="radio"]{accent-color:var(--primary-color)}

/* Responsive */
@media (max-width:768px){
  .btn{width:100%;justify-content:center}
  .modal-footer{flex-direction:column}
  .modal-footer .btn{width:100%}
}

/* Hide old header */
.page-head{display:none!important}
</style>
@endpush

@section('content')
<div class="documents-page">
  <div class="page-head d-flex justify-content-between align-items-center">
    <div class="page-indicator">
      <i class="fa-solid fa-briefcase"></i>
      <strong>Edit Job</strong>
      <span class="small ms-1" id="hint">—</span>
    </div>
  </div>

  <div class="stepper my-3" id="stepper">
    <div class="step active" data-step="1"><div class="num">1</div><div class="label">Basics</div></div>
    <div class="step" data-step="2"><div class="num">2</div><div class="label">Description & Media</div></div>
    <div class="step" data-step="3"><div class="num">3</div><div class="label">Schedule & Update</div></div>
  </div>

  {{-- STEP 1: BASICS (col-md-6) --}}
  <div id="s1" class="card-like" style="position:relative">
    <div class="container-fluid px-0">
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label">Title <span class="text-danger">*</span></label>
          <div class="input-group">
            <span class="input-group-text"><i class="fa-solid fa-heading"></i></span>
            <input type="text" class="form-control" id="title" maxlength="200" placeholder="e.g., Implement Payments — Razorpay">
          </div>
          <div class="field-error" data-for="title"></div>
        </div>

        <div class="col-md-6">
          <label class="form-label">Client</label>
          <div class="d-flex gap-2">
            <button type="button" class="btn btn-secondary" id="btnPickClient">
              <i class="fa-regular fa-building me-1"></i>Choose Client
            </button>
            <button type="button" class="btn btn-light" id="clearClient" title="Clear client"><i class="fa-solid fa-xmark"></i></button>
          </div>
          <select class="form-select" id="client_id" style="display:none">
            <option value="">— Select client —</option>
          </select>
          <div class="parent-actions">
            <span class="muted tiny">Current:</span>
            <span id="clientCurrent" class="badge-soft">No client selected</span>
          </div>
          <div class="tiny mt-1">Client filters parent jobs, media and documents.</div>
          <div class="field-error" data-for="client_id"></div>
        </div>

        <div class="col-md-6">
          <label class="form-label">Type</label>
          <div class="input-group">
            <span class="input-group-text"><i class="fa-solid fa-layer-group"></i></span>
            <select class="form-select" id="type"></select>
          </div>
        </div>

        <div class="col-md-6">
          <label class="form-label">Priority</label>
          <div class="input-group">
            <span class="input-group-text"><i class="fa-solid fa-signal"></i></span>
            <select class="form-select" id="priority"></select>
          </div>
        </div>

        <div class="col-md-6">
          <label class="form-label">Status</label>
          <div class="input-group">
            <span class="input-group-text"><i class="fa-regular fa-circle-play"></i></span>
            <select class="form-select" id="status"></select>
          </div>
        </div>

        <div class="col-md-6">
          <label class="form-label">Budget</label>
          <div class="input-group">
            <span class="input-group-text"><i class="fa-solid fa-indian-rupee-sign"></i></span>
            <input type="number" min="0" step="0.01" class="form-control" id="budget" placeholder="e.g., 25000">
          </div>
          <div class="tiny mt-1">Optional overall job budget for planning and billing reference.</div>
          <div class="field-error" data-for="budget"></div>
        </div>

        <div class="col-md-6">
          <label class="form-label">Parent Job</label>
          <div class="d-flex gap-2">
            <button type="button" class="btn btn-secondary" id="btnPickParent">
              <i class="fa-solid fa-sitemap me-1"></i>Select Parent
            </button>
            <button type="button" class="btn btn-light" id="clearParent" title="Clear parent"><i class="fa-solid fa-xmark"></i></button>
          </div>
          <input type="hidden" id="parent_id">
          <div class="parent-actions">
            <span class="muted tiny">Current:</span>
            <span id="parentCurrent" class="badge-soft">Self (Root)</span>
          </div>
        </div>
      </div>

      <div class="d-flex justify-content-between mt-4">
        <a href="/admin/jobs/view" class="btn btn-secondary js-cancel">Cancel</a>
        <button id="to2" class="btn btn-primary">Proceed</button>
      </div>
    </div>
  </div>

  {{-- STEP 2: DESCRIPTION & MEDIA (full-width) --}}
  <div id="s2" class="card-like mt-3" style="display:none; position:relative">
    <div class="toolbar-row">
      <span class="toolbar-title">Visual</span>
      <button type="button" class="tool-btn" data-cmd="bold" title="Bold"><i class="fa-solid fa-bold"></i></button>
      <button type="button" class="tool-btn" data-cmd="italic" title="Italic"><i class="fa-solid fa-italic"></i></button>
      <button type="button" class="tool-btn" data-cmd="underline" title="Underline"><i class="fa-solid fa-underline"></i></button>
      <button type="button" class="tool-btn" data-format="H1" title="H1">H1</button>
      <button type="button" class="tool-btn" data-format="H2" title="H2">H2</button>
      <button type="button" class="tool-btn" data-format="H3" title="H3">H3</button>
      <button type="button" class="tool-btn" data-cmd="insertUnorderedList" title="Bulleted list"><i class="fa-solid fa-list-ul"></i></button>
      <button type="button" class="tool-btn" data-cmd="insertOrderedList" title="Numbered list"><i class="fa-solid fa-list-ol"></i></button>
      <button type="button" class="tool-btn" id="btnLink" title="Insert Link"><i class="fa-solid fa-link"></i></button>
      <button type="button" class="tool-btn" id="btnImg" title="Insert Image"><i class="fa-regular fa-image"></i></button>
      <span class="toolbar-divider"></span>
      <span class="tiny muted">Upload or pick from library and Insert. Double-click an image to insert immediately.</span>
    </div>

    <div id="editor" class="editor-area" contenteditable="true" spellcheck="true" aria-label="Job description editor"></div>

    <div class="mt-4">
      <div class="form-check form-switch">
        <input class="form-check-input" type="checkbox" id="useDocument">
        <label class="form-check-label" for="useDocument">Link a Document</label>
      </div>

      <div id="docRow" class="mt-2" style="display:none">
        <label class="form-label">Document</label>
        <div class="input-group">
          <span class="input-group-text"><i class="fa-regular fa-file-lines"></i></span>
          <select class="form-select" id="document_id" disabled>
            <option value="">— Select document —</option>
          </select>
          <button class="btn btn-secondary" id="btnLoadFromDoc" type="button" title="Load description from document" disabled>
            <i class="fa-solid fa-download me-1"></i>Use description
          </button>

          {{-- ✅ NEW: Add Document (same as Create page) --}}
          <button class="btn btn-outline-secondary" id="btnAddDocument" type="button" title="Upload a new document for this client" disabled>
            <i class="fa-solid fa-file-circle-plus me-1"></i>Add document
          </button>
        </div>
        <div class="tiny mt-1">Client filter applies. You can also upload a new document and it will appear here.</div>
      </div>
    </div>

    <div class="d-flex justify-content-between mt-4">
      <a href="/admin/jobs/view" class="btn btn-secondary js-cancel">Cancel</a>
      <div style="display:flex;gap:12px;">
        <button id="back1" class="btn btn-light" type="button">&larr; Back</button>
        <button id="to3" class="btn btn-primary" type="button">Proceed</button>
      </div>
    </div>
  </div>

  {{-- STEP 3: SCHEDULE & UPDATE (col-md-6) --}}
  <div id="s3" class="card-like mt-3" style="display:none; position:relative">
    <div class="overlay-dim" id="busyOverlay"><div class="spinner" aria-label="Working…"></div></div>

    <div class="d-flex align-items-center gap-2 mb-3">
      <div class="form-check form-switch">
        <input class="form-check-input" type="checkbox" id="allowWeekend" title="Allow weekend scheduling">
        <label class="form-check-label" for="allowWeekend">Allow weekend scheduling</label>
      </div>
      <span class="tiny muted">By default jobs cannot start or end on Saturday or Sunday.</span>
    </div>

    <div class="container-fluid px-0">
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label">Planned Start</label>
          <div class="input-group">
            <span class="input-group-text"><i class="fa-regular fa-calendar-days"></i></span>
            <input type="date" class="form-control" id="planned_start_at">
          </div>
          <div class="tiny mt-1">Past dates are allowed when editing an existing job.</div>
          <div class="field-error" data-for="planned_start_at"></div>
        </div>

        <div class="col-md-6">
          <label class="form-label">Planned End</label>
          <div class="input-group">
            <span class="input-group-text"><i class="fa-regular fa-calendar-check"></i></span>
            <input type="date" class="form-control" id="planned_end_at">
          </div>
          <div class="tiny mt-1">Must be after start. Selecting End auto-fills Duration.</div>
          <div class="field-error" data-for="planned_end_at"></div>
        </div>

        <div class="col-md-6">
          <label class="form-label">Duration (days)</label>
          <div class="input-group">
            <span class="input-group-text"><i class="fa-regular fa-clock"></i></span>
            <input type="number" min="0" class="form-control" id="planned_duration_days" placeholder="e.g., 3">
          </div>
          <div class="tiny mt-1">Changing this will auto-calc End if Start is set.</div>
        </div>

        <div class="col-md-6">
          <label class="form-label">Deadline (optional)</label>
          <div class="input-group">
            <span class="input-group-text"><i class="fa-regular fa-hourglass-half"></i></span>
            <input type="date" class="form-control" id="planned_deadline_at">
          </div>
          <div class="tiny mt-1">Saved in IST (Asia/Kolkata).</div>
        </div>

        <div class="col-12 d-flex justify-content-between">
          <a href="/admin/jobs/view" class="btn btn-secondary js-cancel">Cancel</a>
          <div style="display:flex;gap:12px;">
            <button id="back2" class="btn btn-light" type="button">&larr; Back</button>
            <button id="btnUpdate" class="btn btn-primary" type="button">
              <span class="busy-label"><i class="fa-solid fa-pen me-2"></i>Update Job</span>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- Insert Image Modal (Upload + Library + optional URL) --}}
  <div class="modal fade" id="imgModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h6 class="modal-title"><i class="fa-regular fa-image me-2"></i>Insert Image</h6>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <!-- Upload (pretty tile) -->
          <h6 class="mb-2">Upload</h6>
          <form id="uploadFormLocal" class="mb-3" onsubmit="return false;">
            <div class="row g-3">
              <div class="col-md-12">
                <label class="form-label">Title <span class="tiny text-muted">(optional)</span></label>
                <input type="text" class="form-control" name="title" maxlength="160">
              </div>
              <div class="col-md-12">
                <label class="form-label">Image</label>
                <div class="upload-tile" id="uploadTile">
                  <i class="fa-regular fa-image icon"></i>
                  <div class="text">
                    <b>Click to choose</b>
                    <span>JPG, PNG, GIF, WEBP • up to 5MB</span>
                  </div>
                </div>
                <input type="file" class="d-none" id="uploadInput" name="file" accept=",.jpg,.jpeg,.png,.gif,.webp,image/*">
              </div>
              <div class="col-12" id="uploadPreviewWrap" style="display:none">
                <div class="upload-preview">
                  <img id="uploadPreview" alt="">
                </div>
              </div>
            </div>
            <div class="progress mt-2" role="progressbar" aria-label="Upload progress">
              <div class="progress-bar" id="uploadProgress" style="width:0%">0%</div>
            </div>
            <div class="mt-2 d-flex gap-2">
              <button type="button" class="btn btn-primary" id="btnUploadLocal" disabled>
                <span class="busy-label"><i class="fa fa-upload me-2"></i>Upload</span>
              </button>
              <button type="button" class="btn btn-light" id="btnUploadReset">Reset</button>
            </div>
            <div class="tiny text-muted mt-1">After successful upload, the image is auto-inserted into the editor.</div>
          </form>

          <!-- Library -->
          <h6 class="mt-3 mb-2">Library</h6>
          <div id="imgLibLoading" class="row-inline mb-2" style="display:none">
            <div class="spinner"></div><span>Loading…</span>
          </div>
          <div class="d-flex gap-2 mb-2">
            <button type="button" class="btn btn-secondary btn-sm" id="btnRefreshLib"><i class="fa fa-rotate me-1"></i>Refresh</button>
            <span class="tiny muted">Click once to select, double-click to insert.</span>
          </div>
          <div id="imgLibGrid" class="grid media"></div>

          <!-- Optional URL (not required) -->
          <h6 class="mt-4 mb-2">Or paste URL (optional)</h6>
          <div class="row g-2">
            <div class="col-12">
              <label class="form-label">Image URL (absolute)</label>
              <input type="url" class="form-control" id="imgUrl" placeholder="https://…">
            </div>
            <div class="col-md-6">
              <label class="form-label">Width (px)</label>
              <input type="number" class="form-control" id="imgW" min="1" placeholder="e.g., 800">
            </div>
            <div class="col-md-6">
              <label class="form-label">Height (px)</label>
              <input type="number" class="form-control" id="imgH" min="1" placeholder="e.g., 450">
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-light" data-bs-dismiss="modal">Close</button>
          <button class="btn btn-primary" id="btnInsertImg"><span class="busy-label"><i class="fa-solid fa-check me-1"></i>Insert</span></button>
        </div>
      </div>
    </div>
  </div>

  {{-- ✅ NEW: Add Document Modal (same behavior as Create page) --}}
  <div class="modal fade" id="docCreateModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h6 class="modal-title"><i class="fa-regular fa-file-lines me-2"></i>Add Document</h6>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">
          <form id="docCreateForm" onsubmit="return false;">
            <div class="row g-3">
              <div class="col-md-12">
                <label class="form-label">Client</label>
                <input type="text" class="form-control" id="docClientName" readonly>
                <input type="hidden" id="docClientId">
                <div class="tiny mt-1">Client is preselected from “Edit Job”.</div>
              </div>

              <div class="col-md-12">
                <label class="form-label">Document Type <span class="text-danger">*</span></label>
                <select class="form-select" id="docType">
                  <option value="">— Select type —</option>
                </select>
                <div class="field-error" data-for="doc_type"></div>
              </div>

              <div class="col-md-6">
                <label class="form-label">Issue Date <span class="text-danger">*</span></label>
                <input type="date" class="form-control" id="docIssueDate">
                <div class="field-error" data-for="issue_date"></div>
              </div>

              <div class="col-md-6">
                <label class="form-label">Expiry Date <span class="text-danger">*</span></label>
                <input type="date" class="form-control" id="docExpiryDate">
                <div class="field-error" data-for="expiry_date"></div>
              </div>

              <div class="col-md-12">
                <label class="form-label">Issuing Authority <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="docIssuingAuthority" maxlength="200" placeholder="e.g., Govt. Authority / Company / Institute">
                <div class="field-error" data-for="issuing_authority"></div>
              </div>

              <div class="col-md-12">
                <label class="form-label">Choose File <span class="text-danger">*</span></label>

                <div class="dropzone" id="docDropzone">
                  <i class="fa-regular fa-file icon"></i>
                  <div class="text">
                    <b>Click to choose or drag & drop</b>
                    <span>PDF/DOC/DOCX/XLS/XLSX/PPT/PPTX/JPG/PNG • recommended ≤ 20MB</span>
                  </div>
                </div>

                <input type="file" class="d-none" id="docFile"
                  accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,image/*">

                <div id="docFilePill" class="file-pill mt-2" style="display:none">
                  <div class="meta">
                    <b id="docFileName">—</b>
                    <small id="docFileSize">—</small>
                  </div>
                  <button type="button" class="btn btn-light" id="docFileRemove" style="height:36px;padding:0 14px">
                    <i class="fa-solid fa-xmark"></i> Remove
                  </button>
                </div>

                <div class="field-error" data-for="file"></div>

                <div class="progress mt-2" role="progressbar" aria-label="Document upload progress">
                  <div class="progress-bar" id="docUploadProgress" style="width:0%">0%</div>
                </div>

                <div class="tiny text-muted mt-1">After upload, the document list will refresh and auto-select the new document.</div>
              </div>
            </div>
          </form>
        </div>

        <div class="modal-footer">
          <button class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
          <button class="btn btn-primary" id="btnDocUpload">
            <span class="busy-label"><i class="fa-solid fa-cloud-arrow-up me-1"></i>Upload Document</span>
          </button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="clientModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h6 class="modal-title"><i class="fa-regular fa-building me-2"></i>Select Client</h6>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="tiny muted mb-2">Choose a client from the hierarchy.</div>
          <div id="clientLoad" class="row-inline mb-2" style="display:none"><div class="spinner"></div><span>Loading clients…</span></div>
          <ul id="clientTree" class="parent-tree"></ul>
          <div class="tiny muted">Tip: Click ▶ to expand children.</div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
          <button class="btn btn-primary" id="btnSaveClient"><i class="fa-solid fa-check me-1"></i>Use Client</button>
        </div>
      </div>
    </div>
  </div>

  {{-- Parent Picker Modal (beautified tree) --}}
  <div class="modal fade" id="parentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h6 class="modal-title"><i class="fa-solid fa-sitemap me-2"></i>Select Parent Job</h6>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="tiny muted mb-2">Single selection. Only jobs of the selected client are listed.</div>
          <div id="parentLoad" class="row-inline mb-2" style="display:none"><div class="spinner"></div><span>Loading hierarchy…</span></div>
          <ul id="parentTree" class="parent-tree"></ul>
          <div class="tiny muted">Tip: Click ▶ to expand children.</div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
          <button class="btn btn-primary" id="btnSaveParent"><i class="fa-solid fa-check me-1"></i>Save Parent</button>
        </div>
      </div>
    </div>
  </div>

  {{-- Toasts --}}
  <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index:1080">
    <div id="toastSuccess" class="toast align-items-center text-bg-success border-0" role="alert">
      <div class="d-flex"><div class="toast-body" id="toastSuccessText">Done</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div>
    </div>
    <div id="toastError" class="toast align-items-center text-bg-danger border-0 mt-2" role="alert">
      <div class="d-flex"><div class="toast-body" id="toastErrorText">Something went wrong</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div>
    </div>
    <div id="toastInfo" class="toast align-items-center text-bg-primary border-0 mt-2" role="alert">
      <div class="d-flex"><div class="toast-body" id="toastInfoText">Copied</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
(function(){
  /* TOASTS */
  const toastSuccess = new bootstrap.Toast(document.getElementById('toastSuccess'));
  const toastError   = new bootstrap.Toast(document.getElementById('toastError'));
  const toastInfo    = new bootstrap.Toast(document.getElementById('toastInfo'));
  const ok  = (m)=>{ document.getElementById('toastSuccessText').textContent = m || 'Done'; toastSuccess.show(); };
  const err = (m)=>{ document.getElementById('toastErrorText').textContent = m || 'Something went wrong'; toastError.show(); };
  const info= (m)=>{ document.getElementById('toastInfoText').textContent = m || 'Info'; toastInfo.show(); };

  /* CANCEL CONFIRM */
  function confirmCancel() {
    return Swal.fire({
      title: 'Are you sure?', text: 'Any unsaved changes will be lost.', icon: 'warning',
      showCancelButton: true, confirmButtonColor: '#d33', cancelButtonColor: '#3085d6',
      confirmButtonText: 'Yes, cancel', cancelButtonText: 'No, keep editing'
    });
  }
  document.addEventListener('click', function (e) {
    const link = e.target.closest && e.target.closest('.js-cancel');
    if (!link) return; e.preventDefault();
    const target = link.getAttribute('data-href') || link.getAttribute('href') || '/admin/jobs';
    confirmCancel().then(r => { if (r.isConfirmed) location.href = target; });
  });

  /* AUTH / API */
  const TOKEN = localStorage.getItem('token') || sessionStorage.getItem('token') || '';
  const headers = { 'Authorization':'Bearer '+TOKEN, 'Accept':'application/json' };
  if(!TOKEN){ Swal.fire('Auth Required','Session expired. Please login again.','warning').then(()=>location.href='/'); return; }

  // Get job id from path
  const urlParts = window.location.pathname.split('/').filter(Boolean);
  const jobId = urlParts[urlParts.length-1] || new URLSearchParams(window.location.search).get('id');
  if(!jobId){ Swal.fire('Error','Job ID not found.','error').then(()=>location.href='/admin/jobs/view'); return; }

  const API = {
    enums: '/api/job-details/enums',
    clients: '/api/clients/all?status=active&sort=asc',
    jobsIndex: '/api/job-details',
    mediaList: '/api/job-details/media',
    mediaUpload: '/api/job-details/media',
    updateJob: `/api/job-details/${jobId}`,
    getJob: `/api/job-details/${jobId}`,
    docsAll1: '/api/documents/all?status=active&sort=asc',
    docsAll2: '/api/documents',
    docShow:   (id)=>`/api/documents/${id}`,

    // ✅ NEW (same as Create page)
    docUpload: '/api/documents/uploads',
    docStore:  '/api/documents',
    docTypes:  '/api/doctypes'
  };

  const esc=(s='')=>String(s).replace(/[&<>\"']/g, m=>({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;' }[m] ));
  const byId=(id)=>document.getElementById(id);

  const steps = {
    bar: byId('stepper'),
    s1: byId('s1'), s2: byId('s2'), s3: byId('s3'),
    to2: byId('to2'), to3: byId('to3'),
    back1: byId('back1'), back2: byId('back2')
  };

  const fields = {
    title: byId('title'),
    type: byId('type'),
    priority: byId('priority'),
    status: byId('status'),
    budget: byId('budget'),
    client: byId('client_id'),
    clientCurrent: byId('clientCurrent'),
    btnPickClient: byId('btnPickClient'),
    clearClient: byId('clearClient'),

    useDocument: byId('useDocument'),
    docRow: byId('docRow'),
    document: byId('document_id'),
    btnLoadFromDoc: byId('btnLoadFromDoc'),
    btnAddDocument: byId('btnAddDocument'),

    parentId: byId('parent_id'),
    parentCurrent: byId('parentCurrent'),
    editor: byId('editor'),
    btnLink: byId('btnLink'),
    btnImg: byId('btnImg'),

    startAt: byId('planned_start_at'),
    endAt: byId('planned_end_at'),
    duration: byId('planned_duration_days'),
    deadline: byId('planned_deadline_at'),
    allowWeekend: byId('allowWeekend'),

    btnUpdate: byId('btnUpdate'),
    btnPickParent: byId('btnPickParent')
  };

  const hint = byId('hint');
  const overlay = byId('busyOverlay');
  const imgModal = new bootstrap.Modal(byId('imgModal'));
  const clientModal = new bootstrap.Modal(byId('clientModal'));
  const parentModal = new bootstrap.Modal(byId('parentModal'));
  const clientTreeEl = byId('clientTree');
  const clientLoadEl = byId('clientLoad');
  let clientRowsCache = [];
  let selectedClientNode = null;

  // ✅ NEW: document create modal
  const docCreateModalEl = byId('docCreateModal');
  const docCreateModal = new bootstrap.Modal(docCreateModalEl);

  const docUI = {
    form: byId('docCreateForm'),
    clientName: byId('docClientName'),
    clientId: byId('docClientId'),
    type: byId('docType'),
    issue: byId('docIssueDate'),
    expiry: byId('docExpiryDate'),
    authority: byId('docIssuingAuthority'),
    dropzone: byId('docDropzone'),
    fileInput: byId('docFile'),
    filePill: byId('docFilePill'),
    fileName: byId('docFileName'),
    fileSize: byId('docFileSize'),
    fileRemove: byId('docFileRemove'),
    btnUpload: byId('btnDocUpload'),
    progress: byId('docUploadProgress')
  };

  /* Fancy upload input refs */
  const uploadForm = byId('uploadFormLocal');
  const uploadTile = byId('uploadTile');
  const uploadInput = byId('uploadInput');
  const uploadPreviewWrap = byId('uploadPreviewWrap');
  const uploadPreview = byId('uploadPreview');
  const btnUpload = byId('btnUploadLocal');
  const btnUploadReset = byId('btnUploadReset');
  const bar = byId('uploadProgress');

  function setBusy(on){ overlay.classList.toggle('show', !!on); }
  function btnBusy(btn, on){ btn.setAttribute('aria-busy', on ? 'true' : 'false'); btn.disabled = !!on; }
  function showError(field, msg){
    const el = document.querySelector(`.field-error[data-for="${field}"]`);
    if (!el) return; el.textContent = msg || ''; el.style.display = msg ? 'block' : 'none';
  }
  function clearErrors(){ document.querySelectorAll('.field-error').forEach(e=>{ e.textContent=''; e.style.display='none'; }); }

  // ✅ NEW: doc modal errors (same as Create page)
  function showDocError(field, msg){
    const el = docCreateModalEl.querySelector(`.field-error[data-for="${field}"]`);
    if (!el) return; el.textContent = msg || ''; el.style.display = msg ? 'block' : 'none';
  }
  function clearDocErrors(){
    docCreateModalEl.querySelectorAll('.field-error').forEach(e=>{ e.textContent=''; e.style.display='none'; });
  }

  /* STEPPER */
  function goto(n){
    const nodes = Array.from(steps.bar.querySelectorAll('.step'));
    nodes.forEach((el,i)=>{ el.classList.toggle('active', i===n-1); el.classList.toggle('done', i<n-1); });
    steps.s1.style.display = n===1?'block':'none';
    steps.s2.style.display = n===2?'block':'none';
    steps.s3.style.display = n===3?'block':'none';
    localStorage.setItem('job:lastStep', String(n));
    hint.textContent = n===1 ? 'Edit the basics.' : n===2 ? 'Update description and media.' : 'Adjust dates and update.';
  }
  steps.bar.addEventListener('click', (e)=>{
    const node = e.target.closest('.step'); if(!node) return;
    const n = Number(node.dataset.step||1);
    if (n===1) return goto(1);
    if (n===2) return goto(2);
    if (n===3) return goto(3);
  });
  steps.to2.addEventListener('click', ()=> goto(2));
  steps.back1.addEventListener('click', ()=> goto(1));
  steps.to3.addEventListener('click', ()=> goto(3));
  steps.back2.addEventListener('click', ()=> goto(2));

  /* DATA LOADERS */
  async function fetchJSON(url){
    const r=await fetch(url,{headers}); let j={}; try{ j=await r.json(); }catch{}
    if(!r.ok) throw new Error(j.message||('HTTP '+r.status)); return j;
  }
  async function loadEnums(){
    const j = await fetchJSON(API.enums);
    const e = j?.data||{};
    fillOptions(fields.type, e.types, 'task');
    fillOptions(fields.priority, e.priority, 'normal');
    fillOptions(fields.status, e.status, 'planned');
  }
  function fillOptions(sel, arr, def){
    sel.innerHTML = (arr||[]).map(v=>`<option value="${esc(v)}"${v===def?' selected':''}>${v.replaceAll('_',' ')}</option>`).join('');
  }
  function getSelectedClientName(){
    const sel = fields.client;
    const idx = sel ? sel.selectedIndex : -1;
    return (idx >= 0 && sel.options[idx]) ? String(sel.options[idx].textContent || '').trim() : '';
  }
  function syncClientCurrentLabel(){
    const name = getSelectedClientName();
    fields.clientCurrent.textContent = name || 'No client selected';
  }
  function toClientTree(rows){
    const map = new Map();
    rows.forEach(r=> map.set(String(r.id), { id:r.id, title:String(r.name || (`Client #${r.id}`)).trim(), parent_id:r.parent_id || null, children:[] }));
    const roots = [];
    rows.forEach(r=>{
      const node = map.get(String(r.id));
      if (r.parent_id && map.has(String(r.parent_id))){
        map.get(String(r.parent_id)).children.push(node);
      } else {
        roots.push(node);
      }
    });
    const sortRec = (arr)=>{ arr.sort((a,b)=>a.title.localeCompare(b.title)); arr.forEach(n=>sortRec(n.children)); };
    sortRec(roots);
    return roots;
  }
  function renderClientTree(nodes, container){
    container.innerHTML='';
    const liRoot = document.createElement('li');
    const itemRoot = document.createElement('div'); itemRoot.className='parent-item';
    const fakeT = document.createElement('button'); fakeT.type='button'; fakeT.className='toggle'; fakeT.style.visibility='hidden'; fakeT.innerHTML='<i class="fa-solid fa-chevron-right"></i>';
    const radioRoot = document.createElement('input'); radioRoot.type='radio'; radioRoot.name='clientPick'; radioRoot.value='';
    if (!fields.client.value) radioRoot.checked = true;
    const titleRoot = document.createElement('div'); titleRoot.className='parent-title';
    titleRoot.innerHTML='<strong>No client selected</strong> <span class="chip">Clear selection</span>';
    itemRoot.appendChild(fakeT); itemRoot.appendChild(radioRoot); itemRoot.appendChild(titleRoot);
    liRoot.appendChild(itemRoot); container.appendChild(liRoot);
    radioRoot.addEventListener('change', ()=>{ selectedClientNode = null; });
    nodes.forEach(node=> container.appendChild(renderClientNode(node)));
  }
  function renderClientNode(node){
    const li = document.createElement('li');
    const item = document.createElement('div'); item.className='parent-item';
    const toggle = document.createElement('button'); toggle.type='button'; toggle.className='toggle'; toggle.innerHTML='<i class="fa-solid fa-chevron-right"></i>'; toggle.title='Expand';
    if (!node.children || !node.children.length) toggle.style.visibility='hidden';
    const radio = document.createElement('input'); radio.type='radio'; radio.name='clientPick'; radio.value=String(node.id);
    if (fields.client.value && String(node.id)===String(fields.client.value)) radio.checked = true;
    const title = document.createElement('div'); title.className='parent-title';
    title.innerHTML = `<strong>${esc(node.title)}</strong> <span class="chip">#${node.id}${node.parent_id?' • child':''}</span>`;
    item.appendChild(toggle); item.appendChild(radio); item.appendChild(title);
    li.appendChild(item);
    const kids = document.createElement('ul'); kids.className='parent-children parent-tree';
    li.appendChild(kids);

    if (node.children && node.children.length){
      node.children.forEach(ch=> kids.appendChild(renderClientNode(ch)));
      toggle.addEventListener('click', ()=>{
        const open = kids.style.display==='block';
        kids.style.display = open?'none':'block';
        toggle.classList.toggle('open', !open);
      });
    }
    radio.addEventListener('change', ()=>{ selectedClientNode = { id: node.id, title: node.title }; });
    return li;
  }
  async function loadClients(){
    try{
      const j = await fetchJSON(API.clients);
      const rows = Array.isArray(j.data) ? j.data : [];
      clientRowsCache = rows;
      fields.client.innerHTML = '<option value="">— Select client —</option>' + rows.map(c=>`<option value="${c.id}">${esc(c.name||('Client #'+c.id))}</option>`).join('');
      syncClientCurrentLabel();
    }catch{ err('Failed to load clients'); }
  }

  // Robust helper — returns yyyy-mm-dd suitable for <input type="date">
  function normalizeDateForInput(s){
    if (!s) return '';
    s = String(s).trim();

    const dateOnly = s.match(/^(\d{4})-(\d{2})-(\d{2})$/);
    if (dateOnly) return `${dateOnly[1]}-${dateOnly[2]}-${dateOnly[3]}`;

    const tIndex = s.indexOf('T');
    if (tIndex > 0){
      const left = s.slice(0, tIndex);
      if (/^\d{4}-\d{2}-\d{2}$/.test(left)) return left;
    }

    const spaceIndex = s.indexOf(' ');
    if (spaceIndex > 0){
      const left = s.slice(0, spaceIndex);
      if (/^\d{4}-\d{2}-\d{2}$/.test(left)) return left;
    }

    const d = new Date(s);
    if (isNaN(d)) return '';
    const y = d.getFullYear();
    const m = d.getMonth() + 1;
    const day = d.getDate();
    const p = (n) => n < 10 ? '0' + n : String(n);
    return `${y}-${p(m)}-${p(day)}`;
  }

  /* Load job data and prefill fields */
  async function loadJob(){
    try{
      const j = await fetchJSON(API.getJob);
      const data = j.data || {};

      fields.title.value = data.title || '';
      if (data.type) fields.type.value = data.type;
      if (data.priority) fields.priority.value = data.priority;
      if (data.status) fields.status.value = data.status;
      fields.budget.value = data.budget != null ? String(data.budget) : '';

      if (data.client_id) fields.client.value = String(data.client_id);
      else fields.client.value = '';
      syncClientCurrentLabel();

      // parent
      if (data.parent_id && data.parent_id !== 'self'){
        fields.parentId.value = data.parent_id;
        fields.parentCurrent.textContent = `#${data.parent_id}`;
        try{
          const pj = await fetchJSON(`/api/job-details/${data.parent_id}`);
          fields.parentCurrent.textContent = `#${data.parent_id}: ${pj.data.title||'Unknown'}`;
        }catch{}
      } else {
        fields.parentId.value = '';
        fields.parentCurrent.textContent = 'Self (Root)';
      }

      fields.editor.innerHTML = data.description || '';

      fields.startAt.value = normalizeDateForInput(data.planned_start_at);
      fields.endAt.value   = normalizeDateForInput(data.planned_end_at);
      fields.duration.value = data.planned_duration_days != null ? String(data.planned_duration_days) : '';
      fields.deadline.value = normalizeDateForInput(data.planned_deadline_at);

      // Documents: if linked, enable UI, load docs and select saved id
      if (data.document_id){
        fields.useDocument.checked = true;
        fields.docRow.style.display = 'block';
        fields.document.disabled = false;

        try {
          await loadDocuments();
          fields.document.value = String(data.document_id);
          fields.btnLoadFromDoc.disabled = false;
        } catch (e) {
          console.warn('Could not load documents when pre-filling job edit', e);
          info('Could not load documents (check network).');
          fields.btnLoadFromDoc.disabled = false;
        }
      } else {
        fields.useDocument.checked = false;
        fields.docRow.style.display = 'none';
        fields.document.disabled = true;
        fields.btnLoadFromDoc.disabled = true;
      }

      // enable Add Document when appropriate
      if (fields.btnAddDocument){
        fields.btnAddDocument.disabled = !(fields.useDocument.checked && !!fields.client.value);
      }

      const serverAllow = !!data.allow_weekend;
      const rangeHas = (fields.startAt.value && fields.endAt.value) ? rangeHasWeekend(fields.startAt.value, fields.endAt.value) : false;
      fields.allowWeekend.checked = serverAllow || rangeHas;

      steps.to2.disabled = false;
    }catch(e){
      console.error(e); err('Failed to load job');
    }
  }

  fields.client.addEventListener('change', ()=>{
    syncClientCurrentLabel();
    fields.parentId.value=''; fields.parentCurrent.textContent='Self (Root)';
    if (fields.useDocument.checked) loadDocuments();
    if (fields.btnAddDocument){
      fields.btnAddDocument.disabled = !(fields.useDocument.checked && !!fields.client.value);
    }
    refreshMedia();
  });

  fields.btnPickClient.addEventListener('click', async ()=>{
    try{
      if (!clientRowsCache.length) await loadClients();
      selectedClientNode = fields.client.value ? { id: fields.client.value, title: getSelectedClientName() } : null;
      renderClientTree(toClientTree(clientRowsCache), clientTreeEl);
      clientModal.show();
    }catch{
      err('Failed to load clients');
    }
  });

  fields.clearClient.addEventListener('click', ()=>{
    fields.client.value = '';
    fields.client.dispatchEvent(new Event('change'));
  });

  byId('btnSaveClient').addEventListener('click', ()=>{
    fields.client.value = selectedClientNode ? String(selectedClientNode.id) : '';
    fields.client.dispatchEvent(new Event('change'));
    clientModal.hide();
  });

  /* Documents */
  fields.useDocument.addEventListener('change', toggleDocUI);
  fields.document.addEventListener('change', ()=>{ byId('btnLoadFromDoc').disabled = !fields.document.value; });
  byId('btnLoadFromDoc').addEventListener('click', async ()=>{
    const id = fields.document.value; if(!id) return;
    try{
      const r = await fetchJSON(API.docShow(id));
      const desc = r?.data?.description || r?.data?.doc_description || r?.description || '';
      if (desc){ fields.editor.innerHTML = desc; ok('Description loaded from document'); }
      else { info('No description found on document'); }
    }catch{ err('Unable to load document details'); }
  });

  function toggleDocUI(arg){
    const skipLoad = (arg === true); // only true skips load (avoid event-object truthiness)
    const on = !!fields.useDocument.checked;

    fields.docRow.style.display = on ? 'block' : 'none';
    fields.document.disabled = !on;
    byId('btnLoadFromDoc').disabled = !(on && fields.document.value);

    if (fields.btnAddDocument){
      fields.btnAddDocument.disabled = !(on && !!fields.client.value);
    }

    if (on && !skipLoad) loadDocuments();
  }

  async function loadDocuments(){
    const cid = fields.client.value;
    fields.document.innerHTML = '<option value="">— Select document —</option>';
    if (!cid){ info('Pick a client to list documents'); return; }

    try{
      const j = await fetchJSON(API.docsAll1 + '&client_id=' + encodeURIComponent(cid));
      const rows = Array.isArray(j.data) ? j.data : [];
      if (!rows.length) throw new Error('fallback');
      fillDocs(rows);
    } catch {
      try{
        const j2 = await fetchJSON(API.docsAll2 + '?client_id=' + encodeURIComponent(cid));
        const rows2 = Array.isArray(j2.data) ? j2.data : [];
        fillDocs(rows2);
      } catch {
        err('Failed to load documents');
      }
    }

    function fillDocs(rows){
      fields.document.innerHTML =
        '<option value="">— Select document —</option>' +
        rows.map(d=>`<option value="${d.id}">${esc(d.doc_name||d.name||('Doc #'+d.id))}</option>`).join('');
    }
  }

  /* ✅ NEW: Add Document modal flow (same as Create page) */
  let docTypesLoaded = false;

  async function loadDocTypesOnce(){
    if (docTypesLoaded) return;
    try{
      const j = await fetchJSON(API.docTypes);
      const rows = Array.isArray(j.data) ? j.data : [];
      docUI.type.innerHTML =
        '<option value="">— Select type —</option>' +
        rows.map(t=>{
          const id = t.id;
          const name = String(t.name || ('Type #'+id)).trim();
          return `<option value="${esc(id)}">${esc(name)}</option>`;
        }).join('');
      docTypesLoaded = true;
    } catch (e){
      docUI.type.innerHTML = '<option value="">— Failed to load types —</option>';
      docTypesLoaded = true;
      err('Failed to load document types (/api/doctypes)');
    }
  }

  function setMinDatesForDocModal(){
    const d = new Date();
    const pad=(n)=>n<10?('0'+n):String(n);
    const min = `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}`;
    docUI.issue.min = min;
    docUI.expiry.min = min;
  }

  docUI.issue.addEventListener('change', ()=>{
    if (docUI.issue.value) docUI.expiry.min = docUI.issue.value;
    if (docUI.issue.value && docUI.expiry.value && docUI.expiry.value < docUI.issue.value){
      docUI.expiry.value = '';
      info('Expiry date must be after Issue date');
    }
  });

  function formatBytes(bytes){
    if (!bytes && bytes !== 0) return '';
    const sizes=['B','KB','MB','GB']; let i=0; let v=bytes;
    while (v>=1024 && i<sizes.length-1){ v/=1024; i++; }
    return (i===0? v : v.toFixed(1)) + ' ' + sizes[i];
  }
  function setDocFile(file){
    docUI._file = file;
    docUI.fileName.textContent = file.name || 'Selected file';
    docUI.fileSize.textContent = formatBytes(file.size || 0);
    docUI.filePill.style.display = 'flex';
  }
  function resetDocFileUI(){
    docUI._file = null;
    docUI.fileInput.value = '';
    docUI.filePill.style.display = 'none';
    docUI.fileName.textContent = '—';
    docUI.fileSize.textContent = '—';
  }

  docUI.dropzone.addEventListener('click', ()=> docUI.fileInput.click());
  docUI.fileInput.addEventListener('change', ()=>{
    const f = docUI.fileInput.files && docUI.fileInput.files[0];
    if (!f){ resetDocFileUI(); return; }
    setDocFile(f);
  });
  docUI.fileRemove.addEventListener('click', resetDocFileUI);

  ;['dragenter','dragover'].forEach(ev=>{
    docUI.dropzone.addEventListener(ev, (e)=>{
      e.preventDefault(); e.stopPropagation();
      docUI.dropzone.classList.add('dragover');
    });
  });
  ;['dragleave','drop'].forEach(ev=>{
    docUI.dropzone.addEventListener(ev, (e)=>{
      e.preventDefault(); e.stopPropagation();
      docUI.dropzone.classList.remove('dragover');
    });
  });
  docUI.dropzone.addEventListener('drop', (e)=>{
    const f = e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files[0];
    if (!f) return;
    setDocFile(f);
  });

  function swalLoading(title = 'Please wait…', text = 'Updating document dropdown…') {
    Swal.fire({
      title,
      text,
      allowOutsideClick: false,
      allowEscapeKey: false,
      showConfirmButton: false,
      didOpen: () => Swal.showLoading(),
    });
    return () => { if (Swal.isVisible()) Swal.close(); };
  }

  async function selectUploadedDocInDropdown(docId, docLabel){
    // ensure link-document UI is ON
    fields.useDocument.checked = true;

    // show doc UI but do NOT auto-load inside toggle
    toggleDocUI(true);

    // load docs now
    await loadDocuments();

    if (docId){
      fields.document.value = String(docId);

      const exists = Array.from(fields.document.options).some(o => String(o.value) === String(docId));
      if (!exists){
        const opt = document.createElement('option');
        opt.value = String(docId);
        opt.textContent = docLabel || `Doc #${docId}`;
        fields.document.appendChild(opt);
      }
      fields.document.value = String(docId);
    } else if (docLabel){
      const opts = Array.from(fields.document.options);
      const found = opts.find(o => String(o.textContent||'').trim() === String(docLabel).trim());
      if (found) fields.document.value = found.value;
    }

    fields.btnLoadFromDoc.disabled = !fields.document.value;
    if (fields.btnAddDocument){
      fields.btnAddDocument.disabled = !(fields.useDocument.checked && !!fields.client.value);
    }
  }

  let isDocUploading = false;

  fields.btnAddDocument.addEventListener('click', async ()=>{
    if (!fields.useDocument.checked) return;
    if (!fields.client.value){ info('Select a client first'); return; }

    clearDocErrors();
    docUI.form.reset();
    resetDocFileUI();
    docUI.progress.style.width = '0%';
    docUI.progress.textContent = '0%';

    const cid = fields.client.value;
    const cname = getSelectedClientName() || '—';

    docUI.clientName.value = cname;
    docUI.clientId.value   = cid;

    setMinDatesForDocModal();
    await loadDocTypesOnce();
    docCreateModal.show();
  });

  docUI.btnUpload.addEventListener('click', async ()=>{
    if (isDocUploading) return;

    clearDocErrors();

    const client_id = docUI.clientId.value;
    const document_type_id = docUI.type.value;
    const issue_date = docUI.issue.value;
    const expiry_date = docUI.expiry.value;
    const issuing_authority = docUI.authority.value.trim();
    const file = docUI._file || (docUI.fileInput.files && docUI.fileInput.files[0]);

    let hasErr = false;
    if (!client_id){ showDocError('client_id','Client is required'); hasErr=true; }
    if (!document_type_id){ showDocError('doc_type','Document type is required'); hasErr=true; }
    if (!issue_date){ showDocError('issue_date','Issue date is required'); hasErr=true; }
    if (!expiry_date){ showDocError('expiry_date','Expiry date is required'); hasErr=true; }
    if (issue_date && expiry_date && expiry_date < issue_date){
      showDocError('expiry_date','Expiry must be after Issue date'); hasErr=true;
    }
    if (!issuing_authority){ showDocError('issuing_authority','Issuing authority is required'); hasErr=true; }
    if (!file){ showDocError('file','Please choose a file'); hasErr=true; }

    if (hasErr) return;

    isDocUploading = true;
    btnBusy(docUI.btnUpload, true);

    try{
      // STEP 1: upload file
      const fdUp = new FormData();
      fdUp.append('folder', 'documents');
      fdUp.append('file', file);

      let uploaded = null;
      await uploadXHR({
        url: API.docUpload,
        headers,
        formData: fdUp,
        onProgress: (pct)=>{
          docUI.progress.style.width = pct + '%';
          docUI.progress.textContent = pct + '%';
        },
        onDone: (j)=>{ uploaded = j; },
        onError: (m)=>{ throw new Error(m || 'Upload failed'); }
      });

      const filePath = uploaded?.path || uploaded?.data?.path || null;
      if (!filePath) throw new Error('Upload succeeded but file path not returned');

      // STEP 2: store document row
      const payload = {
        client_id: parseInt(client_id, 10),
        document_type_id: parseInt(document_type_id, 10),
        doc_name: file.name || 'Document',
        issue_date,
        expiry_date,
        issuing_authority,
        file_url: filePath,
        status: 'active',
      };

      const res = await fetch(API.docStore, {
        method: 'POST',
        headers: { 'Content-Type':'application/json', ...headers },
        body: JSON.stringify(payload)
      });

      const ct = (res.headers.get('content-type')||'').toLowerCase();
      const json = ct.includes('application/json') ? await res.json() : { message: await res.text() };

      if (!res.ok){
        if (res.status === 422 && json.errors){
          Object.entries(json.errors).forEach(([f, arr])=>{
            const msg = Array.isArray(arr) ? arr[0] : String(arr);
            if (f === 'document_type_id') showDocError('doc_type', msg);
            else showDocError(f, msg);
          });
        }
        throw new Error(json.message || ('Store failed: HTTP '+res.status));
      }

      const newId = json?.data?.id || null;
      const newLabel = json?.data?.doc_name || (file.name || '');

      ok('Document uploaded & saved');
      docCreateModal.hide();

      const closeLoading = swalLoading('Updating…', 'Populating document dropdown…');
      try{
        await selectUploadedDocInDropdown(newId, newLabel);
      } finally {
        closeLoading();
      }

      docUI.form.reset();
      resetDocFileUI();
      docUI.progress.style.width = '0%';
      docUI.progress.textContent = '0%';

    } catch (e){
      err(e.message || 'Document upload failed');
    } finally {
      isDocUploading = false;
      btnBusy(docUI.btnUpload, false);
    }
  });

  /* PARENT PICKER — use same modal tree as create */
  const parentTreeEl = byId('parentTree');
  const parentLoadEl = byId('parentLoad');
  let selectedParent = null;
  fields.btnPickParent.addEventListener('click', async ()=>{
    if (!fields.client.value){ info('Select a client first'); return; }
    selectedParent = fields.parentId.value ? { id: fields.parentId.value, title: fields.parentCurrent.textContent } : null;
    parentTreeEl.innerHTML = '';
    await buildParentTreeFromIndex();
    parentModal.show();
  });
  byId('clearParent').addEventListener('click', ()=>{ fields.parentId.value=''; fields.parentCurrent.textContent='Self (Root)'; });

  async function buildParentTreeFromIndex(){
    parentLoadEl.style.display='flex';
    try{ const rows = await fetchAllJobsForClient(fields.client.value); const tree = toTree(rows); renderTree(tree, parentTreeEl); }catch{ err('Failed to load parent jobs'); }
    finally{ parentLoadEl.style.display='none'; }
  }
  async function fetchAllJobsForClient(clientId){
    const out = []; let page = 1, per = 200, safety = 20;
    while (safety-- > 0){
      const qs = new URLSearchParams({ page:String(page), per_page:String(per), client_id:String(clientId), sort:'asc' });
      const url = `${API.jobsIndex}?${qs.toString()}`;
      const j = await fetchJSON(url);
      let rows = Array.isArray(j?.data) ? j.data : (Array.isArray(j) ? j : []);
      if (!rows.length) break;
      rows.forEach(r=>{ out.push({ id: r.id, title: r.title || `Job #${r.id}`, parent_id: r.parent_id || null }); });
      if (rows.length < per) break; page++;
    }
    return out;
  }
  function toTree(rows){
    const map = new Map();
    rows.forEach(r=> map.set(String(r.id), { id:r.id, title:r.title, parent_id:r.parent_id, children:[] }));
    const roots = [];
    rows.forEach(r=>{
      const node = map.get(String(r.id));
      if (r.parent_id && map.has(String(r.parent_id))){
        map.get(String(r.parent_id)).children.push(node);
      } else {
        roots.push(node);
      }
    });
    const sortRec = (arr)=>{ arr.sort((a,b)=>a.title.localeCompare(b.title)); arr.forEach(n=>sortRec(n.children)); };
    sortRec(roots);
    return roots;
  }
  function renderTree(nodes, container){
    container.innerHTML='';
    const liRoot = document.createElement('li');
    const itemRoot = document.createElement('div'); itemRoot.className='parent-item';
    const fakeT = document.createElement('button'); fakeT.type='button'; fakeT.className='toggle'; fakeT.style.visibility='hidden'; fakeT.innerHTML='<i class="fa-solid fa-chevron-right"></i>';
    const radioRoot = document.createElement('input'); radioRoot.type='radio'; radioRoot.name='parentPick'; radioRoot.value='self';
    if (!fields.parentId.value) radioRoot.checked = true;
    const titleRoot = document.createElement('div'); titleRoot.className='parent-title';
    titleRoot.innerHTML='<strong>Self (Root)</strong> <span class="chip">No parent</span>';
    itemRoot.appendChild(fakeT); itemRoot.appendChild(radioRoot); itemRoot.appendChild(titleRoot);
    liRoot.appendChild(itemRoot); container.appendChild(liRoot);
    radioRoot.addEventListener('change', ()=>{ selectedParent = null; });

    nodes.forEach(node=> container.appendChild(renderNode(node)));
  }
  function renderNode(node){
    const li = document.createElement('li');
    const item = document.createElement('div'); item.className='parent-item';
    const toggle = document.createElement('button'); toggle.type='button'; toggle.className='toggle'; toggle.innerHTML='<i class="fa-solid fa-chevron-right"></i>'; toggle.title='Expand';
    if (!node.children || !node.children.length) toggle.style.visibility='hidden';
    const radio = document.createElement('input'); radio.type='radio'; radio.name='parentPick'; radio.value=String(node.id);
    if (fields.parentId.value && String(node.id)===String(fields.parentId.value)) radio.checked = true;
    const title = document.createElement('div'); title.className='parent-title';
    title.innerHTML = `<strong>${esc(node.title)}</strong> <span class="chip">#${node.id}${node.parent_id?' • child':''}</span>`;
    item.appendChild(toggle); item.appendChild(radio); item.appendChild(title);
    li.appendChild(item);
    const kids = document.createElement('ul'); kids.className='parent-children parent-tree';
    li.appendChild(kids);

    if (node.children && node.children.length){
      node.children.forEach(ch=> kids.appendChild(renderNode(ch)));
      toggle.addEventListener('click', ()=>{
        const open = kids.style.display==='block';
        kids.style.display = open?'none':'block';
        toggle.classList.toggle('open', !open);
      });
    }
    radio.addEventListener('change', ()=>{ selectedParent = { id: node.id, title: node.title }; });
    return li;
  }
  byId('btnSaveParent').addEventListener('click', ()=>{
    if (selectedParent && selectedParent.id !== 'self'){
      fields.parentId.value = selectedParent.id;
      fields.parentCurrent.textContent = `#${selectedParent.id}: ${selectedParent.title}`;
    } else {
      fields.parentId.value = '';
      fields.parentCurrent.textContent = 'Self (Root)';
    }
    parentModal.hide();
  });

  /* EDITOR TOOLS + CARET */
  document.querySelectorAll('[data-cmd]').forEach(btn=>{
    btn.addEventListener('click', ()=>{
      document.execCommand(btn.getAttribute('data-cmd'), false, null);
      fields.editor.focus();
    });
  });
  document.querySelectorAll('[data-format]').forEach(btn=>{
    btn.addEventListener('click', ()=>{
      document.execCommand('formatBlock', false, btn.getAttribute('data-format'));
      fields.editor.focus();
    });
  });
  fields.btnLink.addEventListener('click', ()=>{
    const url=prompt('Enter URL (https://...)');
    if(url && /^https?:\/\//i.test(url)) document.execCommand('createLink', false, url);
    fields.editor.focus();
  });

  let savedRange=null;
  function saveSelection(){ const sel=window.getSelection(); if(sel && sel.rangeCount>0) savedRange = sel.getRangeAt(0).cloneRange(); }
  function restoreSelection(){ if(!savedRange) return false; fields.editor.focus(); const sel=window.getSelection(); sel.removeAllRanges(); sel.addRange(savedRange); return true; }
  ['keyup','mouseup','blur','focus'].forEach(ev=> fields.editor.addEventListener(ev, saveSelection));

  /* IMAGE MODAL: library selection + insert */
  const imgLibGrid = byId('imgLibGrid');
  const imgLibLoading = byId('imgLibLoading');
  let selectedImgUrl = null;

  byId('btnImg').addEventListener('click', ()=>{
    byId('imgUrl').value=''; byId('imgW').value=''; byId('imgH').value='';
    selectedImgUrl = null;
    loadImgLibrary();
    imgModal.show();
    saveSelection();
  });
  byId('btnRefreshLib').addEventListener('click', loadImgLibrary);

  function selectCard(card, url){
    Array.from(imgLibGrid.querySelectorAll('.media-card.selected')).forEach(c=>c.classList.remove('selected'));
    card.classList.add('selected');
    selectedImgUrl = url || null;
  }
  async function loadImgLibrary(){
    imgLibGrid.innerHTML=''; imgLibLoading.style.display='flex';
    try{
      const query = new URLSearchParams({ page:'1', per_page:'50' });
      if (fields.client.value) query.set('client_id', fields.client.value);
      const j = await fetchJSON(`${API.mediaList}?${query.toString()}`);
      const rows = Array.isArray(j.data)?j.data:[];
      if (!rows.length){ imgLibGrid.innerHTML='<div class="tiny muted">No media found.</div>'; return; }
      rows.forEach(m=>{
        const url = m.absolute_url || '';
        const card = document.createElement('div');
        card.className='media-card';
        card.innerHTML = `
          <img class="media-thumb" src="${esc(url)}" alt="">
          <div class="media-meta tiny">${esc(m.title||'—')}</div>`;
        card.addEventListener('click', ()=> selectCard(card, url));
        card.addEventListener('dblclick', ()=>{ if(url){ insertImageAtCaret(url,'',''); imgModal.hide(); ok('Image inserted'); } });
        imgLibGrid.appendChild(card);
      });
    }catch{
      imgLibGrid.innerHTML='<div class="tiny muted">Failed to load media.</div>';
    }finally{
      imgLibLoading.style.display='none';
    }
  }

  byId('btnInsertImg').addEventListener('click', ()=>{
    const urlSel = selectedImgUrl && /^https?:\/\//i.test(selectedImgUrl) ? selectedImgUrl : null;
    const urlTyp = byId('imgUrl').value.trim();
    const useUrl = urlSel || (urlTyp && /^https?:\/\//i.test(urlTyp) ? urlTyp : null);
    if (!useUrl){ info('Select an image from Library or paste a valid URL.'); return; }
    insertImageAtCaret(useUrl, byId('imgW').value.trim(), byId('imgH').value.trim());
    imgModal.hide();
    ok('Image inserted');
  });

  function insertImageAtCaret(url, w, h){
    const img = document.createElement('img');
    img.src = url; img.alt=''; img.style.maxWidth='100%';
    if(w) img.setAttribute('width', String(parseInt(w,10)));
    if(h) img.setAttribute('height', String(parseInt(h,10)));
    if (!restoreSelection()){
      fields.editor.appendChild(img);
    } else {
      const sel=window.getSelection(); const range=sel.getRangeAt(0);
      range.deleteContents(); range.insertNode(img);
      range.setStartAfter(img); range.setEndAfter(img);
      sel.removeAllRanges(); sel.addRange(range);
    }
    fields.editor.focus();
  }

  /* Fancy upload tile interactions */
  uploadTile.addEventListener('click', ()=> uploadInput.click());
  uploadInput.addEventListener('change', ()=>{
    const f = uploadInput.files && uploadInput.files[0];
    if (!f){ btnUpload.disabled = true; uploadPreviewWrap.style.display='none'; uploadPreview.src=''; return; }
    if (f.size > 5 * 1024 * 1024){ err('File too large. Max 5MB.'); uploadInput.value=''; return; }
    btnUpload.disabled = false;
    const url = URL.createObjectURL(f);
    uploadPreview.src = url;
    uploadPreviewWrap.style.display='block';
  });
  btnUploadReset.addEventListener('click', ()=>{
    uploadForm.reset();
    uploadPreviewWrap.style.display='none';
    uploadPreview.src='';
    btnUpload.disabled=true;
    bar.style.width='0%';
    bar.textContent='0%';
  });

  let isUploading=false;
  btnUpload.addEventListener('click', ()=>{
    if (isUploading) return;
    const fd = new FormData(uploadForm);
    if (!fd.get('file')) { err('Choose an image file'); return; }
    isUploading = true;
    btnBusy(btnUpload, true);
    uploadXHR({
      url: API.mediaUpload, headers, formData: fd,
      onProgress: (pct)=>{ bar.style.width=pct+'%'; bar.textContent=pct+'%'; },
      onDone: (j)=>{
        const url = j?.data?.absolute_url || j?.data?.url || j?.absolute_url || j?.url || '';
        loadImgLibrary();
        if (url && /^https?:\/\//i.test(url)){
          insertImageAtCaret(url,'',''); imgModal.hide(); ok('Uploaded & inserted');
        } else {
          ok('Uploaded'); info('Select the image from Library to insert.');
        }
        uploadForm.reset();
        uploadPreviewWrap.style.display='none';
        uploadPreview.src='';
        btnUpload.disabled=true;
        bar.style.width='0%';
        bar.textContent='0%';
      },
      onError: (m)=>{ err(m||'Upload failed'); }
    }).finally(()=>{ isUploading=false; btnBusy(btnUpload, false); });
  });

  function uploadXHR({url, headers, formData, onProgress, onDone, onError}){
    return new Promise((resolve)=>{
      const xhr = new XMLHttpRequest();
      xhr.open('POST', url, true);
      Object.entries(headers||{}).forEach(([k,v])=> xhr.setRequestHeader(k, v));
      xhr.onreadystatechange = function(){
        if (xhr.readyState===4){
          try{
            const j = JSON.parse(xhr.responseText||'{}');
            if (xhr.status>=200 && xhr.status<300){
              onDone && onDone(j);
            } else {
              onError && onError(j.message||'Upload failed', j, xhr.status);
            }
          }catch(_){
            if (xhr.status>=200 && xhr.status<300){
              onDone && onDone({});
            } else {
              onError && onError('Upload failed', null, xhr.status);
            }
          }
          resolve();
        }
      };
      if (xhr.upload && onProgress){
        xhr.upload.onprogress = function(e){
          if (e.lengthComputable){
            const pct = Math.round((e.loaded / e.total) * 100);
            onProgress(pct);
          }
        };
      }
      xhr.send(formData);
    });
  }

  /* DATES + WEEKEND RULES */
  function pad(n){ return n<10?('0'+n):String(n); }
  function setMinDates(){ // in edit we allow past dates
    fields.startAt.removeAttribute('min');
    fields.endAt.removeAttribute('min');
    fields.deadline.removeAttribute('min');
    try {
      const startHint = fields.startAt.parentElement.nextElementSibling;
      if (startHint && startHint.classList && startHint.classList.contains('tiny')) {
        startHint.textContent = 'Past dates are allowed when editing an existing job.';
      }
    } catch (e) {}
  }
  setMinDates();

  function parseLocalInput(val){ if(!val) return null; const [y,m,d] = val.split('-').map(Number); return new Date(y, m-1, d); }
  function daysBetweenISO(a,b){ const A=parseLocalInput(a), B=parseLocalInput(b); if(!A||!B) return null; const diff = Math.round((B - A)/(1000*60*60*24)); return diff>=0?diff:null; }
  function isWeekendDate(val){ const d=parseLocalInput(val); if(!d) return false; const day = d.getDay(); return day===0 || day===6; }
  function ensureWeekendRule(input){
    if (!input.value) return;
    if (!fields.allowWeekend.checked && isWeekendDate(input.value)){
      Swal.fire({icon:'warning', title:'Weekend blocked', text:'Weekends are disabled by default. Enable "Allow weekend scheduling" to proceed.'});
      input.value=''; input.focus();
    }
  }
  function rangeHasWeekend(startVal, endVal){
    if(!startVal || !endVal) return false;
    const s = parseLocalInput(startVal), e = parseLocalInput(endVal);
    if(!s || !e || e < s) return false;
    const d = new Date(s.getTime());
    while (d <= e){
      const day=d.getDay();
      if (day===0||day===6) return true;
      d.setDate(d.getDate()+1);
    }
    return false;
  }
  function checkRangeAndWarn(changedInput){
    if (fields.allowWeekend.checked) return;
    if (!fields.startAt.value || !fields.endAt.value) return;
    if (rangeHasWeekend(fields.startAt.value, fields.endAt.value)){
      Swal.fire({icon:'warning', title:'Weekend in selected range', text:'The selected range contains a weekend. Enable "Allow weekend scheduling" or choose weekday-only dates.'});
      if (changedInput && changedInput.value){ changedInput.value=''; ensureOrder(); }
    }
  }
  function ensureOrder(){
    const s=parseLocalInput(fields.startAt.value), e=parseLocalInput(fields.endAt.value);
    if(s && e && e < s){ showError('planned_end_at','End must be after or equal to start.'); }
    else { showError('planned_end_at',''); }
    if (fields.startAt.value){
      fields.endAt.min = fields.startAt.value;
      fields.deadline.min = fields.startAt.value;
    }
  }

  fields.startAt.addEventListener('change', ()=>{
    ensureWeekendRule(fields.startAt); ensureOrder(); autoEndFromDuration(); checkRangeAndWarn(fields.startAt);
    if (fields.endAt.value){ const d = daysBetweenISO(fields.startAt.value, fields.endAt.value); if (d!==null) fields.duration.value = d; }
  });
  fields.endAt.addEventListener('change', ()=>{
    ensureWeekendRule(fields.endAt); ensureOrder(); checkRangeAndWarn(fields.endAt);
    if (fields.startAt.value){ const d = daysBetweenISO(fields.startAt.value, fields.endAt.value); if (d!==null) fields.duration.value = d; }
  });
  fields.deadline.addEventListener('change', ()=>{ ensureWeekendRule(fields.deadline); ensureOrder(); checkRangeAndWarn(fields.deadline); });
  fields.allowWeekend.addEventListener('change', ()=>{
    if (fields.startAt.value) ensureWeekendRule(fields.startAt);
    if (fields.endAt.value) ensureWeekendRule(fields.endAt);
    if (fields.deadline.value) ensureWeekendRule(fields.deadline);
    if (!fields.allowWeekend.checked){ checkRangeAndWarn(); }
  });
  fields.duration.addEventListener('input', autoEndFromDuration);
  function autoEndFromDuration(){
    const start = parseLocalInput(fields.startAt.value);
    const days = parseInt(fields.duration.value||'',10);
    if (start && !Number.isNaN(days)){
      const end = new Date(start.getTime());
      end.setDate(end.getDate()+days);
      const iso = `${end.getFullYear()}-${pad(end.getMonth()+1)}-${pad(end.getDate())}`;
      fields.endAt.value = iso;
      ensureOrder();
    }
  }

  /* UPDATE */
  function toMySqlLocal(val){ const d=parseLocalInput(val); if(!d) return null; const p2=(n)=>n<10?'0'+n:n; return `${d.getFullYear()}-${p2(d.getMonth()+1)}-${p2(d.getDate())}`; }

  async function updateJob(){
    clearErrors();
    if(!fields.title.value.trim()){ showError('title','Title is required.'); goto(1); fields.title.focus(); return; }

    if (!fields.allowWeekend.checked){
      const singleHasWeekend = isWeekendDate(fields.startAt.value) || isWeekendDate(fields.endAt.value) || isWeekendDate(fields.deadline.value||'');
      const rangeHas = rangeHasWeekend(fields.startAt.value, fields.endAt.value);
      if (singleHasWeekend || rangeHas){
        goto(3);
        Swal.fire({icon:'warning', title:'Weekend blocked', text:'The selected dates include a weekend. Enable "Allow weekend scheduling" or pick weekday-only dates.'});
        return;
      }
    }

    const payload = {
      title: fields.title.value.trim(),
      description: fields.editor.innerHTML.trim() || null,
      type: fields.type.value || 'task',
      priority: fields.priority.value || 'normal',
      status: fields.status.value || 'planned',
      budget: fields.budget.value ? parseFloat(fields.budget.value) : null,
      client_id: fields.client.value ? parseInt(fields.client.value,10) : null,
      document_id: (fields.useDocument.checked && fields.document.value) ? parseInt(fields.document.value,10) : null,
      parent_id: fields.parentId.value ? parseInt(fields.parentId.value,10) : null,
      planned_start_at: fields.startAt.value ? toMySqlLocal(fields.startAt.value) : null,
      planned_end_at: fields.endAt.value ? toMySqlLocal(fields.endAt.value) : null,
      planned_deadline_at: fields.deadline.value ? toMySqlLocal(fields.deadline.value) : null,
      planned_duration_days: fields.duration.value ? parseInt(fields.duration.value,10) : null,
      allow_weekend: !!fields.allowWeekend.checked
    };

    Object.keys(payload).forEach(k=>{ if (payload[k]===null || payload[k]==='' || Number.isNaN(payload[k])) delete payload[k]; });

    btnBusy(fields.btnUpdate, true); setBusy(true);
    try{
      const r = await fetch(API.updateJob, {
        method:'PATCH',
        headers: { 'Content-Type':'application/json', ...headers },
        body: JSON.stringify(payload)
      });
      const ct=(r.headers.get('content-type')||'').toLowerCase();
      const json = ct.includes('application/json') ? await r.json() : {message: await r.text()};
      if (r.ok){
        ok('Job updated successfully');
        setTimeout(()=>{ window.location.href='/admin/jobs/view'; },800);
      }
      else if (r.status===422){
        const errors = json.errors || {};
        Object.entries(errors).forEach(([f,arr])=> showError(f, Array.isArray(arr)?arr[0]:String(arr)));
        err(json.message||'Please fix the highlighted fields');
        if (errors.planned_start_at || errors.planned_end_at || errors.planned_deadline_at) goto(3);
      }
      else if (r.status===403){ err('Unauthorized'); }
      else { console.error('Server error', json); err(`Server error (${r.status})`); }
    }catch(ex){
      console.error(ex);
      err('Network error');
    }finally{
      btnBusy(fields.btnUpdate, false); setBusy(false);
    }
  }
  fields.btnUpdate.addEventListener('click', updateJob);

  /* MEDIA LIB REFRESH (reuse create flow) */
  async function refreshMedia(){
    const imgLibGridLocal = document.getElementById('imgLibGrid');
    const imgLibLoadingLocal = document.getElementById('imgLibLoading');
    imgLibLoadingLocal.style.display='flex';
    try{
      const query = new URLSearchParams({ page:'1', per_page:'50' });
      if (fields.client.value) query.set('client_id', fields.client.value);
      const j = await fetchJSON(`${API.mediaList}?${query.toString()}`);
      const rows = Array.isArray(j.data)?j.data:[];
      renderMedia(rows);
    }catch{
      err('Failed to load media');
    }finally{
      imgLibLoadingLocal.style.display='none';
    }
  }
  function renderMedia(rows){
    const g = document.getElementById('mediaGrid');
    if(!g) return;
    g.innerHTML = '';
    if (!rows.length){
      g.innerHTML = '<div class="tiny muted">No media found. Use Upload to add.</div>';
      return;
    }
    rows.forEach(m=>{
      const card = document.createElement('div');
      card.className='media-card';
      card.innerHTML = `
        <img class="media-thumb" src="${esc(m.absolute_url||'')}" alt="">
        <div class="media-meta tiny">${esc(m.title||'—')}</div>
        <div class="media-actions">
          <button class="btn btn-sm btn-light flex-grow-1" data-copy="${esc(m.absolute_url||'')}"><i class="fa-regular fa-clipboard me-1"></i>Copy URL</button>
          <button class="btn btn-sm btn-outline-secondary" data-insert="${esc(m.absolute_url||'')}"><i class="fa-regular fa-image"></i></button>
        </div>`;
      card.querySelector('img').addEventListener('click', ()=> copyToClipboard(m.absolute_url||''));
      card.querySelector('[data-copy]').addEventListener('click', ()=> copyToClipboard(m.absolute_url||''));
      card.querySelector('[data-insert]').addEventListener('click', ()=>{ insertImageAtCaret(m.absolute_url||'', '', ''); });
      document.getElementById('mediaGrid').appendChild(card);
    });
  }
  async function copyToClipboard(text){
    if(!text) return;
    try{
      await navigator.clipboard.writeText(text);
      info('Copied media URL');
    }catch{
      const ta=document.createElement('textarea');
      ta.value=text; document.body.appendChild(ta);
      ta.select(); document.execCommand('copy');
      ta.remove();
      info('Copied media URL');
    }
  }

  /* INIT */
  async function init(){
    hint.textContent = 'Edit the job.';
    steps.to2.disabled = false;
    await loadEnums();
    await loadClients();
    await loadJob();
    refreshMedia();
    goto(1);
  }
  init();
})();
</script>
@endpush
