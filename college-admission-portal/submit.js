const downloads = [
  'assets/grades_form_1.pdf',
  'assets/grades_form_2.pdf'
];

document.querySelectorAll('.grade-card').forEach((card, index) => {
  card.addEventListener('click', () => {
    const link = document.createElement('a');
    link.href = downloads[index];
    link.download = downloads[index].split('/').pop();
    link.click();
  });
});

// ====== Map pages to step index ======
const pageToStep = {
  "index.html": 0,
  "readfirst.html": 1,
  "confirmation.html": 2,
  "aap.html": 3,
  "personal.html": 4,
  "educattach.html": 5,
  "programs.html": 6,
  "form.html": 7,
  "submit.html": 8,
};

// ====== Get current page ======
const currentPage = window.location.pathname.split("/").pop();

// ====== Load progress safely ======
let savedStep = parseInt(localStorage.getItem("currentStep"));
let currentStep = pageToStep[currentPage] !== undefined ? pageToStep[currentPage] : (savedStep || 0);
let maxUnlockedStep = parseInt(localStorage.getItem("maxUnlockedStep")) || currentStep;

// Update maxUnlockedStep if current page is further than saved progress
if (currentStep > maxUnlockedStep) {
  maxUnlockedStep = currentStep;
}

document.addEventListener("DOMContentLoaded", () => {
  const steps = document.querySelectorAll(".step");

  // ====== Update step UI ======
  function updateSteps() {
    steps.forEach((step, index) => {
      // ACTIVE step (highlighted)
      step.classList.toggle("active", index === currentStep);

      // Make all steps up to maxUnlockedStep clickable
      if (index <= maxUnlockedStep) {
        step.classList.add("clickable");
        step.style.pointerEvents = "auto";
        step.style.opacity = "1";
        step.style.cursor = "pointer";
      } else {
        // Locked steps
        step.classList.remove("clickable");
        step.style.pointerEvents = "none";
        step.style.opacity = "0.5";
        step.style.cursor = "not-allowed";
      }
    });

    // Save progress
    localStorage.setItem("currentStep", currentStep);
    localStorage.setItem("maxUnlockedStep", maxUnlockedStep);
  }

  // ====== Step click navigation ======
  steps.forEach((step, index) => {
    step.addEventListener("click", () => {
      // Allow navigation to any unlocked step (including backward navigation)
      if (index > maxUnlockedStep) {
        console.log('Step locked:', index);
        return;
      }

      currentStep = index;
      updateSteps();

      // Navigate to the corresponding page
      const pageMap = [
        "index.html",
        "readfirst.html",
        "confirmation.html",
        "aap.html",
        "personal.html",
        "educattach.html",
        "programs.html",
        "form.html",
        "submit.html"
      ];

      if (pageMap[index]) {
        window.location.href = pageMap[index];
      }
    });
  });

  // ====== Initial render ======
  updateSteps();
});

// --- signature functionality (file upload + draw) ---
const fileInput = document.getElementById('fileInput');
const chooseFileBtn = document.getElementById('chooseFileBtn');
const fileName = document.getElementById('fileName');
const signatureImage = document.getElementById('signatureImage');
const placeholder = document.getElementById('placeholder');
const certifyCheckbox = document.getElementById('certifyCheckbox');
const submitBtn = document.getElementById('submitBtn');
const drawBtn = document.getElementById('drawBtn');
const canvas = document.getElementById('signatureCanvas');

if (!canvas) {
  console.warn('Signature canvas not found - signature functionality disabled.');
} else {
  let ctx = canvas.getContext('2d');
  let isDrawing = false;
  let drawMode = false;
  let hasSignature = false;

  function fitSignatureCanvas() {
    const box = document.getElementById('signatureBox');
    if (!box || !canvas) return;
    const rect = box.getBoundingClientRect();
    const dpr = window.devicePixelRatio || 1;

    canvas.width = Math.round(rect.width * dpr);
    canvas.height = Math.round(rect.height * dpr);

    canvas.style.width = rect.width + 'px';
    canvas.style.height = rect.height + 'px';

    ctx = canvas.getContext('2d');
    ctx.setTransform(dpr, 0, 0, dpr, 0, 0);

    ctx.lineCap = 'round';
    ctx.lineJoin = 'round';
    ctx.strokeStyle = '#000';
    ctx.lineWidth = 2;
  }

  window.addEventListener('DOMContentLoaded', fitSignatureCanvas);
  window.addEventListener('resize', fitSignatureCanvas);

  function showCanvas() {
    canvas.style.display = 'block';
    canvas.classList.add('active');
    signatureImage && signatureImage.classList.remove('show');
    placeholder.style.display = 'none';
    fitSignatureCanvas();
  }
  function hideCanvas() {
    canvas.style.display = 'none';
    canvas.classList.remove('active');
  }
  function showImage() {
    if (!signatureImage) return;
    signatureImage.classList.add('show');
    signatureImage.style.display = 'block';
    hideCanvas();
    placeholder.style.display = 'none';
  }
  function hideImage() {
    if (!signatureImage) return;
    signatureImage.classList.remove('show');
    signatureImage.style.display = 'none';
  }
  function showPlaceholder() {
    placeholder.style.display = 'block';
    hideCanvas();
    hideImage();
  }

  function showSuccessNotif() {
    const overlay = document.getElementById("notifOverlay");
    if (overlay) overlay.style.display = "flex";
  }

  function checkSubmitEligibility() {
    const enabled = !!hasSignature && !!(certifyCheckbox && certifyCheckbox.checked);
    if (submitBtn) submitBtn.disabled = !enabled;
  }

  if (chooseFileBtn && fileInput) {
    chooseFileBtn.addEventListener('click', () => fileInput.click());
  }

  if (fileInput) {
    fileInput.addEventListener('change', (e) => {
      const file = e.target.files && e.target.files[0];
      if (!file) return;
      fileName && (fileName.textContent = file.name);
      const reader = new FileReader();
      reader.onload = (ev) => {
        if (signatureImage) signatureImage.src = ev.target.result;
        showImage();
        ctx && ctx.clearRect(0, 0, canvas.width, canvas.height);
        drawMode = false;
        if (drawBtn) {
          drawBtn.innerHTML = `<svg class="pen-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M12 19l7-7 3 3-7 7-3-3z"></path><path d="M18 13l-1.5-7.5L2 2l3.5 14.5L13 18l5-5z"></path><path d="M2 2l7.586 7.586"></path></svg> Draw Signature`;
        }
        hasSignature = true;
        checkSubmitEligibility();
      };
      reader.readAsDataURL(file);
    });
  }

  if (drawBtn) {
    drawBtn.addEventListener('click', () => {
      drawMode = !drawMode;
      if (drawMode) {
        showCanvas();
        hideImage();
        drawBtn.textContent = 'Clear Drawing';
        fitSignatureCanvas();
      } else {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        hideCanvas();
        showPlaceholder();
        drawBtn.innerHTML = `<svg class="pen-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M12 19l7-7 3 3-7 7-3-3z"></path><path d="M18 13l-1.5-7.5L2 2l3.5 14.5L13 18l5-5z"></path><path d="M2 2l7.586 7.586"></path></svg> Draw Signature`;
        hasSignature = false;
        checkSubmitEligibility();
      }
    });
  }

  canvas.style.touchAction = 'none';

  function getCanvasPointCSS(evt) {
    const rect = canvas.getBoundingClientRect();
    const clientX = (evt.clientX !== undefined) ? evt.clientX : (evt.touches && evt.touches[0] && evt.touches[0].clientX);
    const clientY = (evt.clientY !== undefined) ? evt.clientY : (evt.touches && evt.touches[0] && evt.touches[0].clientY);
    return {
      x: clientX - rect.left,
      y: clientY - rect.top
    };
  }

  function pointerDownHandler(e) {
    if (!drawMode) return;
    e.preventDefault();
    if (e.pointerId) canvas.setPointerCapture && canvas.setPointerCapture(e.pointerId);
    isDrawing = true;
    const p = getCanvasPointCSS(e);
    ctx.beginPath();
    ctx.moveTo(p.x, p.y);
  }

  function pointerMoveHandler(e) {
    if (!isDrawing || !drawMode) return;
    e.preventDefault();
    const p = getCanvasPointCSS(e);
    ctx.lineTo(p.x, p.y);
    ctx.stroke();
    if (!hasSignature) {
      hasSignature = true;
      checkSubmitEligibility();
    }
  }

  function pointerUpHandler(e) {
    if (!drawMode) return;
    e.preventDefault();
    if (e.pointerId) canvas.releasePointerCapture && canvas.releasePointerCapture(e.pointerId);
    if (isDrawing) {
      isDrawing = false;
      ctx.closePath();
    }
  }

  canvas.addEventListener('pointerdown', pointerDownHandler);
  canvas.addEventListener('pointermove', pointerMoveHandler);
  canvas.addEventListener('pointerup', pointerUpHandler);
  canvas.addEventListener('pointercancel', pointerUpHandler);
  canvas.addEventListener('pointerleave', pointerUpHandler);

  if (certifyCheckbox) {
  // LOAD SAVED CHECKBOX STATE
  const savedCheck = localStorage.getItem("savedCertify");
  if (savedCheck === "true") {
    certifyCheckbox.checked = true;
  }

  certifyCheckbox.addEventListener('change', () => {
    localStorage.setItem("savedCertify", certifyCheckbox.checked ? "true" : "false");
    checkSubmitEligibility();
  });
}

// =====================================================
// COLLECT ALL FORM DATA FROM LOCALSTORAGE
// =====================================================
function collectAllFormData() {
  const data = {
    personal: {},
    confirmation: {},
    aap: {},
    education: {},
    programs: {},
    signature: null
  };

  // ========== PERSONAL DATA ==========
  data.personal = {
    last_name: localStorage.getItem('lastName') || '',
    first_name: localStorage.getItem('firstName') || '',
    middle_name: localStorage.getItem('middleName') || '',
    name_extension: localStorage.getItem('nameExtension') || '',
    sex: localStorage.getItem('sex') || '',
    birthdate: localStorage.getItem('birthdate') || '',
    nationality: localStorage.getItem('nationality') || '',
    other_nationality: localStorage.getItem('otherNationality') || '',
    height: localStorage.getItem('height') || '',
    region: localStorage.getItem('region') || '',
    province: localStorage.getItem('province') || '',
    city_municipality: localStorage.getItem('city') || '',
    barangay: localStorage.getItem('barangay') || '',
    house_address: localStorage.getItem('houseNo') || '',
    email: localStorage.getItem('email') || '',
    mobile_no: localStorage.getItem('mobile') || '',
    telephone_no: localStorage.getItem('telephone') || '',
    contact_name: localStorage.getItem('contactName') || '',
    contact_address: localStorage.getItem('contactAddress') || '',
    contact_mobile: localStorage.getItem('contactMobile') || '',
    contact_relationship: localStorage.getItem('contactRelationship') || '',
    other_relationship: localStorage.getItem('otherRelationship') || '',
    first_member_to_apply: localStorage.getItem('first_member') || '',
    recipient_of_4ps: localStorage.getItem('4ps') || '',
    member_of_indigenous_group: localStorage.getItem('indigenous') || '',
    indigenous_select: localStorage.getItem('indigenousSelect') || '',
    indigenous_other: localStorage.getItem('indigenousOther') || '',
    member_of_lgbtqia: localStorage.getItem('lgbtqia') || '',
    internally_displaced_person: localStorage.getItem('idp') || '',
    idp_details: localStorage.getItem('idpDetails') || '',
    disability: localStorage.getItem('pwd') || '',
    solo_parent: localStorage.getItem('solo_parent') || '',
    family_income_range: localStorage.getItem('income') || '',
    has_siblings: localStorage.getItem('hasSiblings') || 'no',
    siblings_info: (() => {
      // Get siblings from summary table
      const siblingsSummary = localStorage.getItem('siblingsSummary');
      if (siblingsSummary) {
        try {
          const rows = JSON.parse(siblingsSummary);
          const siblings = [];
          rows.forEach((row, index) => {
            if (index === 0) return; // Skip header
            if (row.length >= 6) {
              siblings.push({
                fullname: row[1] || '',
                age: row[2] || '',
                education: row[3] || '',
                school: row[4] || '',
                year: row[5] || '',
                option: 'N/A'
              });
            }
          });
          return JSON.stringify(siblings);
        } catch (e) {
          console.warn('Failed to parse siblingsSummary:', e);
        }
      }
      return '[]';
    })(),
    student_id_image: localStorage.getItem('savedPhoto') || null
  };
  
  // Add parent names
  const parentNames = getParentNames();
  data.personal.father_name = parentNames.father_name;
  data.personal.mother_name = parentNames.mother_name;
  data.personal.mother_maiden_name = parentNames.mother_maiden_name;
  
  // Add other parent data
  data.personal.father_age = getParentData('father_age');
  data.personal.mother_age = getParentData('mother_age');
  data.personal.father_occupation = getParentData('father_occupation');
  data.personal.mother_occupation = getParentData('mother_occupation');
  data.personal.father_contact_no = getParentData('father_contact_no');
  data.personal.mother_contact_no = getParentData('mother_contact_no');
  
  // ========== CONFIRMATION DATA ==========
  data.confirmation = {
    academic_status: localStorage.getItem('field_academicStatus') || '',
    already_enrolled: localStorage.getItem('field_alreadyEnrolled') || '',
    first_time_applying: localStorage.getItem('field_firstTimeApplying') || '',
    transferred: localStorage.getItem('field_transferred') || '',
    transferred_from: localStorage.getItem('field_transferredFrom') || '',
    transferred_year: localStorage.getItem('field_transferredYear') || '',
    bsu_graduate: localStorage.getItem('field_bsuGraduate') || '',
    bsu_school: localStorage.getItem('field_bsuSchool') || ''
  };

  // ========== AAP DATA ==========
  data.aap = {
    aap_choice: localStorage.getItem('field_aap') || 'none'
  };

  // ========== EDUCATION DATA ==========
  data.education = {
    shs: localStorage.getItem('edu-shsName') || '',
    shs_email: localStorage.getItem('edu-shsEmail') || '',
    school_type: localStorage.getItem('edu-schoolType') || '',
    track: localStorage.getItem('edu-track') || '',
    strand: localStorage.getItem('edu-strand') || localStorage.getItem('selected_strand') || '',
    specialization: localStorage.getItem('edu-specialization') || '',
    junior_hs_completion_year: localStorage.getItem('edu-jhsCompletionYear') || '',
    senior_hs_completion_year: localStorage.getItem('edu-shsCompletionYear') || '',
    category_of_applicant: localStorage.getItem('edu-category') || '',
    final_grades: {
      math: localStorage.getItem('jhs-jhsMath') || '',
      science: localStorage.getItem('jhs-jhsScience') || '',
      english: localStorage.getItem('jhs-jhsEnglish') || ''
    },
    grade_11_records: getGrade11Records(),
    files: {} // Will be populated from file inputs
  };

  // ========== PROGRAM CHOICES ==========
  const savedChoices = localStorage.getItem('programChoices');
  if (savedChoices) {
    try {
      data.programs.choices = JSON.parse(savedChoices);
    } catch (e) {
      data.programs.choices = [];
    }
  } else {
    data.programs.choices = [];
  }

  // ========== SIGNATURE ==========
  const savedSignature = localStorage.getItem('savedSignature');
  if (savedSignature) {
    // Extract base64 data (remove data:image/png;base64, prefix if present)
    data.signature = savedSignature.includes(',')
      ? savedSignature.split(',')[1]
      : savedSignature;
  } else if (canvas && canvas.style.display === 'block') {
    // Get from canvas if drawn
    data.signature = canvas.toDataURL('image/png').split(',')[1];
  }

  return data;
}

// Helper to get parent data from localStorage (stored as parentsData table)
function getParentData(field) {
  // First, try to get from parentsData table in localStorage
  const savedParentsData = localStorage.getItem('parentsData');
  if (savedParentsData) {
    try {
      const parentsTable = JSON.parse(savedParentsData);
      // parentsTable is a 2D array with ALL rows including headers:
      // [0] = First header row: ["Relationship", "Name" (colspan 3), "Age", "Occupation", "Contact No."]
      // [1] = Second header row: ["", "Last Name", "First Name", "Middle Name", "", "", ""]
      // [2] = Father row: ["Father", last, first, middle, age, occupation, contact]
      // [3] = Mother maiden name row: ["Mother" (rowspan), "Mother's Maiden Name" label, maiden name (colspan 3), ...]
      // [4] = Mother row: ["Mother" (rowspan continues), last, first, middle, age, occupation, contact]
      
      // Find data rows by checking if first cell contains "Father" or "Mother"
      let fatherRow = null;
      let motherMaidenRow = null;
      let motherRow = null;
      
      for (let i = 0; i < parentsTable.length; i++) {
        const row = parentsTable[i];
        if (row && row[0]) {
          const firstCell = String(row[0]).trim();
          if (firstCell === 'Father' || firstCell.includes('Father')) {
            fatherRow = row;
          } else if (firstCell === 'Mother' && row[1] && String(row[1]).includes('Maiden')) {
            motherMaidenRow = row;
          } else if (firstCell === 'Mother' && !motherRow && !motherMaidenRow) {
            // This is the mother data row (not the maiden name row)
            motherRow = row;
          } else if (firstCell === 'Mother' && motherMaidenRow && !motherRow) {
            // This is the mother data row (comes after maiden row)
            motherRow = row;
          }
        }
      }
      
      if (fatherRow || motherRow) {
        const fieldMap = {
          'father_last_name': fatherRow?.[1] || '',
          'father_first_name': fatherRow?.[2] || '',
          'father_middle_name': fatherRow?.[3] || '',
          'father_age': fatherRow?.[4] || '',
          'father_occupation': fatherRow?.[5] || '',
          'father_contact_no': fatherRow?.[6] || '',
          'mother_last_name': motherRow?.[1] || '',
          'mother_first_name': motherRow?.[2] || '',
          'mother_middle_name': motherRow?.[3] || '',
          'mother_age': motherRow?.[4] || '',
          'mother_occupation': motherRow?.[5] || '',
          'mother_contact_no': motherRow?.[6] || '',
          'mother_maiden_name': motherMaidenRow?.[2] || '' // Maiden name is in the maiden row, column 2
        };
        
        if (fieldMap[field] !== undefined) {
          const value = fieldMap[field];
          if (value) return value; // Return if we have a value
        }
      }
    } catch (e) {
      console.warn('Failed to parse parentsData from localStorage:', e);
    }
  }
  
  // Second fallback: try to get from parentalData (saved separately when clicking Next)
  const savedParentalData = localStorage.getItem('parentalData');
  if (savedParentalData) {
    try {
      const parentalData = JSON.parse(savedParentalData);
      // parentalData has: fatherFirst (full name), fatherMiddle, fatherLast, motherFirst (full name), etc.
      // Note: fatherFirst and motherFirst are actually full names, not just first names
      const fieldMap = {
        'father_first_name': parentalData.fatherFirst || '', // Actually full name
        'father_middle_name': parentalData.fatherMiddle || '',
        'father_last_name': parentalData.fatherLast || '',
        'father_age': parentalData.fatherAge || '',
        'father_occupation': parentalData.fatherOccupation || '',
        'father_contact_no': parentalData.fatherContact || '',
        'mother_first_name': parentalData.motherFirst || '', // Actually full name
        'mother_middle_name': parentalData.motherMiddle || '',
        'mother_last_name': parentalData.motherLast || '',
        'mother_age': parentalData.motherAge || '',
        'mother_occupation': parentalData.motherOccupation || '',
        'mother_contact_no': parentalData.motherContact || ''
      };
      
      if (fieldMap[field] !== undefined) {
        const value = fieldMap[field];
        if (value) return value;
      }
    } catch (e) {
      console.warn('Failed to parse parentalData from localStorage:', e);
    }
  }
  
  // Third fallback: try to get from DOM if on personal page
  const cell = document.querySelector(`td[data-field="${field}"]`);
  if (cell) {
    const value = cell.textContent.trim();
    if (value) return value;
  }
  
  return '';
}

// Helper to construct full parent names
function getParentNames() {
  // Try to get individual name parts first
  const fatherFirst = getParentData('father_first_name') || '';
  const fatherMiddle = getParentData('father_middle_name') || '';
  const fatherLast = getParentData('father_last_name') || '';
  let fatherName = [fatherFirst, fatherMiddle, fatherLast].filter(Boolean).join(' ').trim();
  
  const motherFirst = getParentData('mother_first_name') || '';
  const motherMiddle = getParentData('mother_middle_name') || '';
  const motherLast = getParentData('mother_last_name') || '';
  let motherName = [motherFirst, motherMiddle, motherLast].filter(Boolean).join(' ').trim();
  
  // If we don't have individual parts, try to get from parentalData (full names)
  if (!fatherName || !motherName) {
    const savedParentalData = localStorage.getItem('parentalData');
    if (savedParentalData) {
      try {
        const parentalData = JSON.parse(savedParentalData);
        // Note: fatherFirst and motherFirst in parentalData are actually full names
        if (!fatherName && parentalData.fatherFirst) {
          fatherName = parentalData.fatherFirst.trim();
        }
        if (!motherName && parentalData.motherFirst) {
          motherName = parentalData.motherFirst.trim();
        }
      } catch (e) {
        console.warn('Failed to parse parentalData:', e);
      }
    }
  }
  
  // Get maiden name - it's optional and non-editable
  const maidenName = getParentData('mother_maiden_name') || '';
  
  // Enhanced debug logging if still missing
  if (!fatherName || !motherName) {
    const savedParentsData = localStorage.getItem('parentsData');
    const savedParentalData = localStorage.getItem('parentalData');
    console.warn('Parent names missing:', {
      fatherFirst, fatherMiddle, fatherLast, fatherName,
      motherFirst, motherMiddle, motherLast, motherName,
      maidenName,
      hasParentsData: !!savedParentsData,
      hasParentalData: !!savedParentalData,
      parentsDataLength: savedParentsData ? JSON.parse(savedParentsData).length : 0
    });
  }
  
  return {
    father_name: fatherName,
    mother_name: motherName,
    mother_maiden_name: maidenName
  };
}

// Helper to get Grade 11 records from localStorage
function getGrade11Records() {
  const records = {};
  
  // Grade 11 records are stored with keys like: shs-g11MathGrade1, shs-g11MathAlt1, etc.
  // Or we can get from the saved eligibilityData
  const eligibilityData = localStorage.getItem('eligibilityData');
  if (eligibilityData) {
    try {
      const parsed = JSON.parse(eligibilityData);
      if (parsed.grade_11_records) {
        return parsed.grade_11_records;
      }
    } catch (e) {
      console.warn('Failed to parse eligibilityData:', e);
    }
  }
  
  // Fallback: construct from localStorage keys
  const config = {
    's1_math': { priority: 'Pre-Calculus', altKey: 'g11MathAlt1', gradeKey: 'g11MathGrade1', naKey: 'g11MathNA1' },
    's1_science': { priority: 'Earth Science', altKey: 'g11ScienceAlt1', gradeKey: 'g11ScienceGrade1', naKey: 'g11ScienceNA1' },
    's1_english': { priority: 'Oral Communication', altKey: 'g11EnglishAlt1', gradeKey: 'g11EnglishGrade1', naKey: 'g11EnglishNA1' },
    's2_math': { priority: 'Basic Calculus', altKey: 'g11MathAlt2', gradeKey: 'g11MathGrade2', naKey: 'g11MathNA2' },
    's2_science': { priority: 'General Chemistry I', altKey: 'g11ScienceAlt2', gradeKey: 'g11ScienceGrade2', naKey: 'g11ScienceNA2' },
    's2_english': { priority: 'Reading and Writing', altKey: 'g11EnglishAlt2', gradeKey: 'g11EnglishGrade2', naKey: 'g11EnglishNA2' }
  };
  
  Object.keys(config).forEach(key => {
    const cfg = config[key];
    records[key] = {
      priority: cfg.priority,
      alternative: localStorage.getItem(`shs-${cfg.altKey}`) || '',
      grade: localStorage.getItem(`shs-${cfg.gradeKey}`) || '',
      na: localStorage.getItem(`shs-${cfg.naKey}`) === 'true' || false
    };
  });
  
  return records;
}

// Helper to convert File objects to Base64
async function fileToBase64(file) {
  return new Promise((resolve, reject) => {
    if (!(file instanceof File) && !(file instanceof Blob)) {
      reject(new Error('Invalid file type'));
      return;
    }
    const reader = new FileReader();
    reader.onload = () => {
      const base64 = reader.result.split(',')[1] || reader.result;
      resolve(base64);
    };
    reader.onerror = reject;
    reader.readAsDataURL(file);
  });
}

// Collect files from localStorage (stored as Base64)
async function collectEducationFiles() {
  const files = {};
  
  // Get files from localStorage (stored as Base64 when uploaded)
  for (let i = 1; i <= 4; i++) {
    const base64 = localStorage.getItem(`file_base64_${i}`);
    if (base64) {
      files[i] = base64;
    } else {
      // Fallback: try to get from file input if still on educattach page
      const fileInput = document.getElementById(`file${i}`);
      if (fileInput && fileInput.files && fileInput.files[0]) {
        try {
          // File still in input, convert it
          const base64 = await fileToBase64(fileInput.files[0]);
          files[i] = base64;
        } catch (e) {
          console.warn(`Failed to convert file ${i} to Base64:`, e);
        }
      }
    }
  }
  
  return files;
}

if (submitBtn) {
  submitBtn.addEventListener('click', async (e) => {
    e.preventDefault();

    if (!hasSignature) {
      alert('Please upload or draw your signature before submitting.');
      return;
    }
    if (!certifyCheckbox || !certifyCheckbox.checked) {
      alert('Please check the certification box.');
      return;
    }

    // Disable button during submission
    submitBtn.disabled = true;
    submitBtn.textContent = 'Submitting...';

    try {
      // Collect all form data
      const formData = collectAllFormData();
      
      // Collect education files from localStorage
      formData.education.files = await collectEducationFiles();
      
      // Validate required files
      const requiredFiles = [1, 2, 3, 4];
      const missingFiles = requiredFiles.filter(i => !formData.education.files[i]);
      if (missingFiles.length > 0) {
        alert('Please upload all required education files before submitting.');
        submitBtn.disabled = false;
        submitBtn.textContent = 'Submit Application';
        return;
      }

      // Send all data to backend
      const res = await fetch('../backend/submit_application.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        credentials: 'same-origin',
        body: JSON.stringify(formData)
      });

      const data = await res.json();
      
      if (!res.ok || !data.success) {
        alert(data.message || 'Failed to submit application. Please try again.');
        submitBtn.disabled = false;
        submitBtn.textContent = 'Submit Application';
        return;
      }

      // Clear localStorage after successful submission
      localStorage.clear();

      // Show application number + PIN to user      
      alert(
        `Your application has been submitted successfully!\n\n` +
        `Application Number: ${data.application_number}\n` +
        `PIN: ${data.pin}\n\n` +
        `Please save these for future reference.`
      );

      // Show success overlay
      const overlay = document.getElementById('notifOverlay');
      if (overlay) {
        overlay.style.display = 'flex';
        setTimeout(() => {
          overlay.style.display = 'none';
          // Redirect to welcome page or a success page
          window.location.href = 'index.html';
        }, 2500);
      }
    } catch (err) {
      console.error('Submission error:', err);
      alert('Network error while submitting application. Please try again.');
      submitBtn.disabled = false;
      submitBtn.textContent = 'Submit Application';
    }
  });
}

  canvas.style.display = 'none';
  if (signatureImage && signatureImage.src) {
    hasSignature = true;
    showImage();
  } else {
    showPlaceholder();
  }

  checkSubmitEligibility();
}

// =====================================================
// SIGNATURE SAVING / RESTORING (Upload + Drawing)
// =====================================================

// Save signature image or canvas drawing
function saveSignature() {
  let data = "";

  if (signatureImage && signatureImage.style.display === "block") {
    // Uploaded image
    data = signatureImage.src;
  } else if (canvas && canvas.style.display === "block") {
    // Drawn signature
    data = canvas.toDataURL("image/png");
  }

  if (data) {
    try {
      localStorage.setItem("savedSignature", data);
    } catch (storageError) {
      // Handle localStorage quota exceeded error
      if (storageError.name === 'QuotaExceededError' || storageError.message.includes('quota') || storageError.message.includes('exceeded')) {
        console.warn('Failed to save signature to localStorage: quota exceeded');
        // Don't show error to user as signature can still be submitted without localStorage
      } else {
        console.warn('Failed to save signature to localStorage:', storageError);
      }
    }
  }
}

// Restore saved signature
function loadSignature() {
  const saved = localStorage.getItem("savedSignature");
  if (!saved) return false;

  // Uploaded Image
  if (!saved.startsWith("data:image/png")) {
    signatureImage.src = saved;
    showImage();
    hasSignature = true;
    return true;
  }

  // Drawn Signature
  const img = new Image();
  img.onload = () => {
    showCanvas();
    fitSignatureCanvas();
    ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
    hasSignature = true;
    checkSubmitEligibility();
  };
  img.src = saved;

  return true;
}