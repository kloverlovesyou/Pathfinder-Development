<script setup>
import { reactive, ref, computed, onMounted, onActivated, nextTick } from "vue";
import jsPDF from "jspdf";
import axios from "axios";
import { useRouter } from "vue-router";
import { useActivityStore } from "@/stores/activityStore";
import { watch } from "vue";

const isModalOpen = ref(false);
const pdfUrl = ref(null);
const newSkill = ref("");
const router = useRouter();
const userName = ref("");
const toasts = ref([]);
const activityStore = useActivityStore();

// --- Forms ---
const showNewExperienceForm = ref(false);
const newExperience = reactive({
  jobTitle: "",
  companyName: "",
  companyAddress: "",
  startYear: new Date().getFullYear(),
  endYear: new Date().getFullYear(),
});

const showNewEducationForm = ref(false);
const newEducation = reactive({
  educationLevel: "",
  program: "",
  major: "",
  strand: "",
  institutionName: "",
  institutionAddress: "",
  graduationYear: null,
});
// ✅ Resume Data
const resume = reactive({
  summary: "",
  experience: [],
  education: [],
  skills: [],
  url: "",
});

// ✅ User Form Data
const form = reactive({
  firstName: "",
  middleName: "",
  lastName: "",
  emailAddress: "",
  phoneNumber: "",
  address: "",
});

// --- Save Resume ---
async function saveResume() {
  try {
    const token = localStorage.getItem("token");
    const response = await axios.post(
      import.meta.env.VITE_API_BASE_URL + "/resume",
      {
        summary: resume.summary,
        professionalLink: resume.url,
      },
      { headers: { Authorization: `Bearer ${token}` } }
    );

    resume.resumeID = response.data.resumeID;
    showToast("Resume saved successfully!");
  } catch (error) {
    console.error("Error saving resume:", error.response?.data || error);
    showToast("Failed to save resume.");
  }
}
function showToast(message, type = "info") {
  const toastId = Date.now();

  toasts.value.push({
    id: toastId,
    message,
    type,
  });

  setTimeout(() => {
    toasts.value = toasts.value.filter((t) => t.id !== toastId);
  }, 3000);
}

// --- Load User Data ---
async function loadUserData() {
  try {
    const token = localStorage.getItem("token");
    if (!token) return;

    const res = await axios.get(import.meta.env.VITE_API_BASE_URL + "/user", {
      headers: { Authorization: `Bearer ${token}` },
    });

    const user = res.data;
    
    // Populate form with user data
    form.firstName = user.firstName || user.first_name || "";
    form.middleName = user.middleName || user.middle_name || "";
    form.lastName = user.lastName || user.last_name || "";
    form.emailAddress = user.emailAddress || user.email || user.email_address || "";
    form.phoneNumber = user.phoneNumber || user.phone || user.phone_number || "";
    form.address = user.address || "";

    // Set userName for display
    if (user.firstName && user.lastName) {
      userName.value = `${user.firstName} ${user.lastName}`.trim();
    }
  } catch (error) {
    console.error("Error loading user data:", error.response?.data || error);
    // Fallback to localStorage if API fails
    const savedUser = localStorage.getItem("user");
    if (savedUser) {
      try {
        const user = JSON.parse(savedUser);
        form.firstName = user.firstName || user.first_name || "";
        form.middleName = user.middleName || user.middle_name || "";
        form.lastName = user.lastName || user.last_name || "";
        form.emailAddress = user.emailAddress || user.email || user.email_address || "";
        form.phoneNumber = user.phoneNumber || user.phone || user.phone_number || "";
        form.address = user.address || "";

        if (user.firstName && user.lastName) {
          userName.value = `${user.firstName} ${user.lastName}`.trim();
        }
      } catch (parseError) {
        console.error("Error parsing saved user data:", parseError);
      }
    }
  }
}

// --- Load Resume ---
async function loadResume() {
  try {
    const token = localStorage.getItem("token");
    const { data } = await axios.get(
      import.meta.env.VITE_API_BASE_URL + "/resume",
      {
        headers: { Authorization: `Bearer ${token}` },
      }
    );

    if (data) {
      resume.summary = data.summary || "";
      resume.url = data.professionalLink || "";
      resume.resumeID = data.resumeID;
    }

    const { data: expData } = await axios.get(
      import.meta.env.VITE_API_BASE_URL + "/experiences",
      {
        headers: { Authorization: `Bearer ${token}` },
      }
    );
    resume.experience = expData || [];

    await loadEducation();
    await loadSkills(resume.resumeID); // ✅ Load skills
  } catch (error) {
    console.error("Error loading resume:", error.response?.data || error);
  }
}
// --- Delete Resume ---
async function deleteResume() {
  try {
    const token = localStorage.getItem("token");
    await axios.delete(import.meta.env.VITE_API_BASE_URL + "/resume", {
      headers: { Authorization: `Bearer ${token}` },
    });

    resume.summary = "";
    resume.url = "";
    showToast("Resume deleted successfully!");
  } catch (error) {
    console.error("Error deleting resume:", error.response?.data || error);
    showToast("Failed to delete resume.");
  }
}

// --- Education ---
async function addEducation() {
  try {
    const token = localStorage.getItem("token");
    const payload = {
      ...newEducation,
      resumeID: resume.resumeID,
    };

    // Remove empty fields
    if (payload.program === "") delete payload.program;
    if (payload.major === "") delete payload.major;
    if (payload.strand === "") delete payload.strand;
    if (payload.graduationYear === "" || payload.graduationYear === null) {
      delete payload.graduationYear;
    }

    console.log("Sending education:", payload);

    if (!resume.resumeID) {
      const resumeRes = await axios.post(
        import.meta.env.VITE_API_BASE_URL + "/resume",
        {
          summary: resume.summary || "",
          professionalLink: resume.url || "",
        },
        { headers: { Authorization: `Bearer ${token}` } }
      );
      resume.resumeID = resumeRes.data.resumeID;
    }

    const { data } = await axios.post(
      import.meta.env.VITE_API_BASE_URL + "/education",
      payload,
      { headers: { Authorization: `Bearer ${token}` } }
    );

    resume.education.push(data);
    Object.keys(newEducation).forEach((key) => {
      if (key === "graduationYear") {
        newEducation[key] = null; // keep as number
      } else {
        newEducation[key] = ""; // string fields
      }
    });
    showNewEducationForm.value = false;
    showToast("Education added successfully!");
  } catch (error) {
    console.error("Error adding education:", error.response?.data || error);
    showToast("Failed to add education.");
  }
}

async function loadEducation() {
  try {
    const token = localStorage.getItem("token");
    const resumeID = resume.resumeID || localStorage.getItem("resumeID");

    const { data } = await axios.get(
      import.meta.env.VITE_API_BASE_URL + `/education?resumeID=${resumeID}`,
      { headers: { Authorization: `Bearer ${token}` } }
    );

    resume.education = data || [];
    console.log("✅ Education Data Loaded:", resume.education);
  } catch (error) {
    console.error("❌ Error loading education:", error.response?.data || error);
  }
}

async function removeEducation(index) {
  try {
    const token = localStorage.getItem("token");
    const edu = resume.education[index];
    if (edu.educationID) {
      await axios.delete(
        import.meta.env.VITE_API_BASE_URL + `/education/${edu.educationID}`,
        {
          headers: { Authorization: `Bearer ${token}` },
        }
      );
    }
    resume.education.splice(index, 1);
  } catch (error) {
    console.error("Error deleting education:", error.response?.data || error);
    showToast("Failed to delete education.");
  }
}

// --- Experience ---
function toDateFromYear(yearInput) {
  const numericYear = Number(yearInput);
  if (!Number.isFinite(numericYear)) {
    return null;
  }
  return `${numericYear}-01-01`;
}

async function addExperience() {
  if (
    !newExperience.jobTitle.trim() ||
    !newExperience.companyName.trim() ||
    !newExperience.companyAddress.trim()
  ) {
    showToast("Please fill out all fields before adding experience.");
    return;
  }

  // Convert to integers and validate
  const startYear = parseInt(newExperience.startYear, 10);
  const endYear = parseInt(newExperience.endYear, 10);

  // Validate years are valid integers in range
  if (!Number.isInteger(startYear) || startYear < 1900 || startYear > 2099) {
    showToast("Start year must be a valid year between 1900 and 2099.");
    return;
  }

  if (!Number.isInteger(endYear) || endYear < 1900 || endYear > 2099) {
    showToast("End year must be a valid year between 1900 and 2099.");
    return;
  }

  if (endYear < startYear) {
    showToast("End year must be greater than or equal to start year.");
    return;
  }

  try {
    const token = localStorage.getItem("token");

    if (!resume.resumeID) {
      const resumeRes = await axios.post(
        import.meta.env.VITE_API_BASE_URL + "/resume",
        {
          summary: resume.summary || "",
          professionalLink: resume.url || "",
        },
        { headers: { Authorization: `Bearer ${token}` } }
      );
      resume.resumeID = resumeRes.data.resumeID;
    }

    const { data } = await axios.post(
      import.meta.env.VITE_API_BASE_URL + "/experiences",
      {
        jobTitle: newExperience.jobTitle,
        companyName: newExperience.companyName,
        companyAddress: newExperience.companyAddress,
        startYear: startYear,
        endYear: endYear,
        resumeID: resume.resumeID,
      },
      { headers: { Authorization: `Bearer ${token}` } }
    );

    resume.experience.push(data);
    Object.assign(newExperience, {
      jobTitle: "",
      companyName: "",
      companyAddress: "",
      startYear: new Date().getFullYear(),
      endYear: new Date().getFullYear(),
    });
    showNewExperienceForm.value = false;
    showToast("Experience added successfully!");
  } catch (error) {
    console.error("Error adding experience:", error.response?.data || error);
    showToast("Failed to add experience.");
  }
}

async function removeExperience(index) {
  try {
    const token = localStorage.getItem("token");
    const exp = resume.experience[index];
    if (exp.experienceID) {
      await axios.delete(
        import.meta.env.VITE_API_BASE_URL + `/experiences/${exp.experienceID}`,
        {
          headers: { Authorization: `Bearer ${token}` },
        }
      );
    }
    resume.experience.splice(index, 1);
  } catch (error) {
    console.error("Error deleting experience:", error.response?.data || error);
    showToast("Failed to delete experience.");
  }
}

// --- Skills ---
async function loadSkills(resumeID) {
  try {
    const token = localStorage.getItem("token");
    const { data } = await axios.get(
      import.meta.env.VITE_API_BASE_URL + `/skills/${resumeID}`,
      {
        headers: { Authorization: `Bearer ${token}` },
      }
    );
    resume.skills = data || [];
    console.log("✅ Skills Loaded:", resume.skills);
  } catch (error) {
    console.error("Error loading skills:", error.response?.data || error);
  }
}

watch(
  () => resume.skills,
  (newSkills) => {
    const skillsArray = newSkills.map(s => s.skillName || s);
    localStorage.setItem(
      "selectedSkills",
      JSON.stringify(skillsArray)
    );
    // Dispatch custom event to notify other components (same window)
    window.dispatchEvent(new Event('skillsUpdated'));
  },
  { deep: true }
);

async function addSkill(skillName) {
  try {
    // If no skillName provided, use the input field value
    const skillToAdd = skillName || newSkill.value.trim();

    if (!skillToAdd || !skillToAdd.trim()) return;

    // Check if skill already exists
    const skillExists = resume.skills.some(s => {
      const existingName = s.skillName || s;
      return existingName && existingName.toLowerCase() === skillToAdd.toLowerCase();
    });

    if (skillExists) {
      showToast("Skill already added!");
      return;
    }

    const token = localStorage.getItem("token");
    if (!token) {
      showToast("Authentication required. Please log in again.");
      return;
    }

    // If no resume exists yet, create one automatically
    if (!resume.resumeID) {
      console.log('No resume found, creating one automatically...');
      try {
        const response = await axios.post(
          import.meta.env.VITE_API_BASE_URL + "/resume",
          {
            summary: resume.summary || "",
            professionalLink: resume.url || "",
          },
          { headers: { Authorization: `Bearer ${token}` } }
        );
        resume.resumeID = response.data.resumeID;
        console.log('Resume created automatically with ID:', resume.resumeID);
      } catch (resumeError) {
        console.error("Error creating resume:", resumeError);
        showToast("Failed to create resume. Please try saving your resume first.");
        return;
      }
    }

    console.log('Adding skill:', skillToAdd, 'to resume:', resume.resumeID);

    const { data } = await axios.post(
      import.meta.env.VITE_API_BASE_URL + "/skills",
      { skillName: skillToAdd.trim(), resumeID: resume.resumeID },
      { headers: { Authorization: `Bearer ${token}` } }
    );

    console.log('API response:', data);

    if (data) {
      // Use Vue.nextTick to ensure reactivity
      await nextTick();
      resume.skills = [...resume.skills, data];
      console.log('Skills updated:', resume.skills.length, 'skills');
      showToast("Skill added successfully!");
    }

    newSkill.value = "";
  } catch (error) {
    console.error("Error adding skill:", error);
    showToast("Failed to add skill. Please try again.");
  }
}

async function removeSkill(index) {
  try {
    const token = localStorage.getItem("token");
    const skill = resume.skills[index];
    if (skill.skillID) {
      await axios.delete(
        import.meta.env.VITE_API_BASE_URL + `/skills/${skill.skillID}`,
        {
          headers: { Authorization: `Bearer ${token}` },
        }
      );
    }
    resume.skills.splice(index, 1);
  } catch (error) {
    console.error("Error removing skill:", error.response?.data || error);
  }
}

// Skills search and selection functions


function handleSkillInput() {
  const query = skillSearchQuery.value.trim();

  if (query) {
    // Filter suggestions based on input
    filteredSuggestions.value = [
      "JavaScript", "Python", "Java", "C++", "C#", "PHP", "Ruby", "Swift", "Kotlin", "Go",
      "React", "Vue.js", "Angular", "Node.js", "Express", "Django", "Spring", "Laravel", "Rails",
      "HTML", "CSS", "SASS", "Bootstrap", "Tailwind CSS", "Material UI",
      "SQL", "MySQL", "PostgreSQL", "MongoDB", "Redis", "Firebase",
      "AWS", "Azure", "Google Cloud", "Docker", "Kubernetes", "Git", "Jenkins", "CI/CD",
      "Machine Learning", "Data Analysis", "TensorFlow", "PyTorch", "Pandas", "NumPy",
      "Agile", "Scrum", "Project Management", "Leadership", "Communication", "Teamwork"
    ].filter(skill => skill.toLowerCase().includes(query.toLowerCase())).slice(0, 8);
    showSuggestions.value = true;
  } else {
    filteredSuggestions.value = [];
    showSuggestions.value = true; // Show popular skills when input is empty
  }
}

function handleSkillFocus() {
  showSuggestions.value = true;
  if (!skillSearchQuery.value.trim()) {
    filteredSuggestions.value = [];
  } else {
    handleSkillInput();
  }
}

function handleSkillBlur() {
  // Delay hiding to allow click events on suggestions
  setTimeout(() => {
    showSuggestions.value = false;
  }, 150);
}

function addCurrentSkill() {
  const query = skillSearchQuery.value.trim();
  if (!query) return;

  addSkill(query);
  skillSearchQuery.value = '';
  showSuggestions.value = false;
}

function selectSkill(skillName) {
  addSkill(skillName);
  skillSearchQuery.value = '';
  showSuggestions.value = false;
}

function hideSuggestions() {
  showSuggestions.value = false;
}






// --- PDF Generation (unchanged from your version) ---
function sectionHeader(doc, title, x, y, pageWidth, margin) {
  doc.setFontSize(14);
  doc.setFont("helvetica", "bold");
  doc.text(title, margin, y);
  doc.setLineWidth(0.5);
  doc.line(margin, y + 2, pageWidth - margin, y + 2);
}

async function generatePdf() {
  const selectedCerts = await fetchSelectedCertificates();
  resume.certificates = selectedCerts; // Attach to resume

  const doc = new jsPDF();
  const pageWidth = doc.internal.pageSize.getWidth();
  const pageHeight = doc.internal.pageSize.getHeight();
  const margin = 20;
  let y = 20;

  function getYearOnly(value) {
    if (!value) return "";

    // Try parsing as date string first
    const parsedDate = new Date(value);
    if (!isNaN(parsedDate.getTime())) {
      return String(parsedDate.getFullYear());
    }

    // Fallback: handle year numbers or numeric strings
    const numericYear = Number(value);
    if (Number.isFinite(numericYear)) {
      return String(Math.trunc(numericYear));
    }

    return String(value);
  }

  function addWrappedText(text, x, y, maxWidth, lineHeight = 6) {
    const lines = doc.splitTextToSize(text, maxWidth);
    for (let line of lines) {
      if (y > pageHeight - margin) {
        doc.addPage();
        y = margin;
      }
      doc.text(line, x, y);
      y += lineHeight;
    }
    return y;
  }

  function sectionHeader(title, x, y, pageWidth, margin) {
    doc.setFont("times", "bold");
    doc.setFontSize(12);
    doc.text(title, x, y);
    y += 2;
    doc.setLineWidth(0.5);
    doc.line(margin, y, pageWidth - margin, y);
    return y + 6;
  }

  // Header
  doc.setFont("times", "bold");
  doc.setFontSize(20);
  const fullName = [form.firstName, form.middleName, form.lastName]
    .filter((name) => name && name.trim() !== "") // remove empty strings or null
    .join(" ");
  const nameLines = doc.splitTextToSize(fullName, pageWidth - 2 * margin);
  nameLines.forEach((line) => {
    if (y > pageHeight - margin) {
      doc.addPage();
      y = margin;
    }
    doc.text(line, pageWidth / 2, y, { align: "center" });
    y += 8;
  });
  y += 4;

  doc.setFont("times", "regular");
  doc.setFontSize(10);
  // Build contact line in order: email, url, phone number, address
  const contactParts = [];
  if (form.emailAddress) contactParts.push(form.emailAddress);
  if (resume.url) contactParts.push(resume.url);
  if (form.phoneNumber) contactParts.push(form.phoneNumber);
  if (form.address) contactParts.push(form.address);
  const contactLine = contactParts.join(" • ");
  const contactLines = doc.splitTextToSize(contactLine, pageWidth - 2 * margin);
  contactLines.forEach((line) => {
    if (y > pageHeight - margin) {
      doc.addPage();
      y = margin;
    }
    doc.text(line, pageWidth / 2, y, { align: "center" });
    y += 6;
  });
  y += 6;

  // Summary
  y = sectionHeader("Summary", margin, y, pageWidth, margin);
  doc.setFont("times", "regular");
  doc.setFontSize(11);
  y = addWrappedText(resume.summary || "", margin, y, pageWidth - 2 * margin);

  // Experience
  if (resume.experience.length) {
    y = sectionHeader("Professional Experience", margin, y, pageWidth, margin);
    const sortedExperiences = [...resume.experience].sort(
      (a, b) =>
        new Date(b.endYear).getFullYear() - new Date(a.endYear).getFullYear()
    );
    sortedExperiences.forEach((exp) => {
      if (y > pageHeight - margin - 20) {
        doc.addPage();
        y = margin;
      }
      const start = getYearOnly(exp.startYear);
      const end = exp.endYear ? getYearOnly(exp.endYear) : "Present";
      const yearText = `${start} – ${end}`;
      
      // Estimate year text width (approximately 40-50 units for "YYYY – YYYY" or "YYYY – Present")
      doc.setFont("times", "bold");
      const yearWidth = doc.getTextWidth(yearText) || 50;
      
      // Job title with wrapping, leaving space for year on the right
      const jobTitleMaxWidth = pageWidth - 2 * margin - yearWidth - 10;
      const jobTitleLines = doc.splitTextToSize(exp.jobTitle || "", Math.max(jobTitleMaxWidth, 50));
      jobTitleLines.forEach((line, index) => {
        if (y > pageHeight - margin) {
          doc.addPage();
          y = margin;
        }
        doc.text(line, margin, y);
        // Only show year on first line of job title
        if (index === 0) {
          doc.text(yearText, pageWidth - margin, y, { align: "right" });
        }
        y += 6;
      });
      
      // Company name with wrapping
      doc.setFont("times", "italic");
      y = addWrappedText(exp.companyName || "", margin, y, pageWidth - 2 * margin);
      
      // Company address with wrapping if it exists
      if (exp.companyAddress) {
        doc.setFont("times", "regular");
        y = addWrappedText(exp.companyAddress, margin, y, pageWidth - 2 * margin);
      }
      y += 4;
    });
  }

  // Education
  if (resume.education.length) {
    y = sectionHeader("Education", margin, y, pageWidth, margin);

    resume.education.forEach((edu) => {
      if (y > pageHeight - margin - 20) {
        doc.addPage();
        y = margin;
      }

      const gradYear = edu.graduationYear?.toString() || "";
      const programLabel =
        edu.program ||
        (edu.educationLevel ? `${edu.educationLevel}` : "") ||
        "";
      const institutionAddress = edu.institutionAddress || "";

      // Institution name with wrapping, leaving space for year on the right
      doc.setFont("times", "bold");
      const yearWidth = gradYear ? (doc.getTextWidth(gradYear) || 30) : 0;
      const institutionMaxWidth = pageWidth - 2 * margin - yearWidth - 10;
      const institutionLines = doc.splitTextToSize(edu.institutionName || "", Math.max(institutionMaxWidth, 50));
      institutionLines.forEach((line, index) => {
        if (y > pageHeight - margin) {
          doc.addPage();
          y = margin;
        }
        doc.text(line, margin, y);
        // Only show year on first line of institution name
        if (index === 0 && gradYear) {
          doc.text(gradYear, pageWidth - margin, y, { align: "right" });
        }
        y += 6;
      });

      doc.setFont("times", "regular");
      if (programLabel) {
        y = addWrappedText(programLabel, margin, y, pageWidth - 2 * margin);
      }
      
      if (institutionAddress) {
        y = addWrappedText(institutionAddress, margin, y, pageWidth - 2 * margin);
      }

      if (edu.major) {
        y = addWrappedText(edu.major, margin, y, pageWidth - 2 * margin);
      }
      
      if (edu.strand) {
        y = addWrappedText(edu.strand, margin, y, pageWidth - 2 * margin);
      }
      
      y += 4;
    });
  }

  // Skills
  if (resume.skills.length) {
    y = sectionHeader("Skills", margin, y, pageWidth, margin);
    doc.setFont("times", "regular");
    doc.setFontSize(11);

    resume.skills.forEach((skill) => {
      if (y > pageHeight - margin - 20) {
        doc.addPage();
        y = margin;
      }

      const bullet = "•";
      const skillName = skill.skillName || skill || "";
      y = addWrappedText(`${bullet} ${skillName}`, margin, y, pageWidth - 2 * margin);
      y += 2;
    });
    y += 4; // Add spacing after skills section
  }

  // Certificates
  if (resume.certificates && resume.certificates.length) {
    y = sectionHeader("Certificates", margin, y, pageWidth, margin);
    doc.setFont("times", "regular");
    doc.setFontSize(11);

    resume.certificates.forEach((cert) => {
      if (y > pageHeight - margin - 20) {
        doc.addPage();
        y = margin;
      }

      // ✅ Add bullet point before certificate name
      const bullet = "•";
      const certName = cert.certificationName || cert.title || "";
      y = addWrappedText(`${bullet} ${certName}`, margin, y, pageWidth - 2 * margin);
      y += 2;
    });
  }

  const pdfBlob = doc.output("blob");
  pdfUrl.value = URL.createObjectURL(pdfBlob);
}

function generateAndOpenPdf() {
  generatePdf();
  isModalOpen.value = true;
}

function closeModal() {
  isModalOpen.value = false;
}

// Fetch popular skills from organizations
async function fetchPopularSkills() {
  try {
    const { data } = await axios.get(
      import.meta.env.VITE_API_BASE_URL + "/popular-skills"
    );
    popularSkills.value = Array.isArray(data) ? data : [];
  } catch (error) {
    console.error('Error fetching popular skills:', error);
    popularSkills.value = [];
  }
}

// --- Autofill + Load Data ---
onMounted(async () => {
  await loadUserData(); // Fetch user data from backend
  await loadResume();
  await activityStore.fetchCounts();
  await fetchSelectedCertificates(); // Ensure certificates are loaded
  await fetchPopularSkills(); // Load popular skills for dropdown


});

const logout = () => {
  localStorage.removeItem("user");
  localStorage.removeItem("token");
  router.push({ name: "Login" });
};

const selectedCertificates = ref([]);



// Skills search and selection with categories for better matchmaking
const skillSearchQuery = ref("");
const showSuggestions = ref(false);
const filteredSuggestions = ref([]);
const popularSkills = ref([]);

async function fetchSelectedCertificates() {
  const token = localStorage.getItem("token");
  const user = JSON.parse(localStorage.getItem("user"));
  if (!token || !user?.applicantID) {
    selectedCertificates.value = [];
    return [];
  }

  try {
    // Fetch all certificates (including organization-issued ones)
    const response = await axios.get(
      import.meta.env.VITE_API_BASE_URL + `/certificates/${user.applicantID}`,
      { headers: { Authorization: `Bearer ${token}` } }
    );

    // Filter for selected certificates (both manual and organization-issued)
    // Only include certificates where IsSelected is 1 or true
    selectedCertificates.value = (response.data || []).filter(
      (cert) => cert.IsSelected === 1 || cert.IsSelected === true
    );

    console.log(
      "✅ Selected certificates loaded:",
      selectedCertificates.value.length
    );

    // ✅ Also return it for PDF preview
    return selectedCertificates.value;
  } catch (error) {
    console.error("❌ Error fetching selected certificates:", error);
    selectedCertificates.value = [];
    return [];
  }
}

// Refresh certificates when component is activated (navigated back to)
onActivated(async () => {
  await fetchSelectedCertificates();
});
</script>

<template>
  <div class="min-h-screen p-3 rounded-lg font-poppins">
    <!--Large screen-->

    <div class="w-full lg:pl-6 flex flex-col gap-6">
      <div class="bg-white p-4 rounded-lg">
        <h2 class="text-2xl font-bold mb-3">Resume Editor</h2>
        <form class="space-y-6">
          <!-- Personal Info -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <input
              v-model="form.firstName"
              type="text"
              placeholder="First Name"
              class="input-field border rounded p-2"
              maxlength="50"
            />
            <input
              v-model="form.emailAddress"
              type="email"
              placeholder="Email"
              class="input-field border rounded p-2"
              maxlength="50"
            />
            <input
              v-model="form.middleName"
              type="text"
              placeholder="Middle Name"
              class="input-field border rounded p-2"
              maxlength="50"
            />
            <input
              v-model="form.phoneNumber"
              type="text"
              placeholder="Mobile Number"
              class="input-field border rounded p-2"
              maxlength="50"
            />
            <input
              v-model="form.lastName"
              type="text"
              placeholder="Last Name"
              class="input-field border rounded p-2"
              maxlength="50"
            />
            <input
              v-model="form.address"
              type="text"
              placeholder="Address"
              class="input-field border rounded p-2"
              maxlength="100"
            />
          </div>

          <!-- URL -->
          <div class="border rounded p-4 space-y-4 relative">
            <h2 class="text-lg font-semibold">Professional Link</h2>
            <div>
              <input
                type="url"
                placeholder="URL"
                class="input-field border rounded p-2 w-full"
                v-model="resume.url"
                maxlength="100"
              />
              <p v-if="resume.url.length >= 100" class="text-xs text-red-500 mt-1">
                Maximum character limit (100) reached
              </p>
              <p v-else-if="resume.url.length > 0" class="text-xs text-gray-500 mt-1">
                {{ 100 - resume.url.length }} characters remaining
              </p>
            </div>
          </div>
          <!-- Professional Summary -->
          <div class="border rounded p-4 space-y-4 relative">
            <label class="block text-lg font-semibold mb-1"
              >Professional Summary</label
            >
            <div class="relative border rounded p-3 rounded-lg">
              <textarea
                rows="3"
                class="input-field w-full"
                v-model="resume.summary"
                maxlength="1000"
              ></textarea>
              <p v-if="resume.summary.length >= 1000" class="text-xs text-red-500 mt-1">
                Maximum character limit (1000) reached
              </p>
              <p v-else-if="resume.summary.length > 0" class="text-xs text-gray-500 mt-1">
                {{ 1000 - resume.summary.length }} characters remaining
              </p>
            </div>
          </div>

          <!-- Professional Experience -->
          <div class="border rounded p-4 space-y-4 relative">
            <!-- Header with Plus Button -->
            <div class="flex justify-between items-center">
              <label class="text-lg font-semibold"
                >Professional Experience</label
              >
              <button
                type="button"
                @click="showNewExperienceForm = true"
                class="w-8 h-8 flex items-center justify-center rounded-full bg-customButton text-white hover:bg-dark-slate"
              >
                +
              </button>
            </div>

            <!-- New Experience Form -->
            <div
              v-if="showNewExperienceForm"
              class="border p-3 rounded space-y-3 mt-3"
            >
              <div>
                <label class="block font-medium mb-1">Job Title <span class="text-red-500">*</span></label>
                <input
                  v-model="newExperience.jobTitle"
                  type="text"
                  placeholder="Enter job title*"
                  class="input-field border rounded w-full p-2"
                  maxlength="100"
                />
                <p v-if="newExperience.jobTitle.length >= 100" class="text-xs text-red-500 mt-1">
                  Maximum character limit (100) reached
                </p>
                <p v-else-if="newExperience.jobTitle.length > 0" class="text-xs text-gray-500 mt-1">
                  {{ 100 - newExperience.jobTitle.length }} characters remaining
                </p>
              </div>
              <div>
                <label class="block font-medium mb-1">Company Name <span class="text-red-500">*</span></label>
                <input
                  v-model="newExperience.companyName"
                  type="text"
                  placeholder="Enter company name*"
                  class="input-field border rounded w-full p-2"
                  maxlength="100"
                />
                <p v-if="newExperience.companyName.length >= 100" class="text-xs text-red-500 mt-1">
                  Maximum character limit (100) reached
                </p>
                <p v-else-if="newExperience.companyName.length > 0" class="text-xs text-gray-500 mt-1">
                  {{ 100 - newExperience.companyName.length }} characters remaining
                </p>
              </div>
              <div>
                <label class="block font-medium mb-1">Company Address <span class="text-red-500">*</span></label>
                <input
                  v-model="newExperience.companyAddress"
                  type="text"
                  placeholder="Enter company address*"
                  class="input-field border rounded w-full p-2"
                  maxlength="100"
                />
                <p v-if="newExperience.companyAddress.length >= 100" class="text-xs text-red-500 mt-1">
                  Maximum character limit (100) reached
                </p>
                <p v-else-if="newExperience.companyAddress.length > 0" class="text-xs text-gray-500 mt-1">
                  {{ 100 - newExperience.companyAddress.length }} characters remaining
                </p>
              </div>
              <div>
                <label class="block font-medium mb-1">Start Year <span class="text-red-500">*</span></label>
                <input
                  v-model="newExperience.startYear"
                  type="number"
                  placeholder="e.g. 2020*"
                  class="input-field border rounded w-full p-2"
                  :max="new Date().getFullYear()"
                />
              </div>
              <div>
                <label class="block font-medium mb-1">End Year <span class="text-red-500">*</span></label>
                <input
                  v-model="newExperience.endYear"
                  type="number"
                  placeholder="e.g. 2022*"
                  class="input-field border rounded w-full p-2"
                  :max="new Date().getFullYear()"
                />
              </div>
              <button
                type="button"
                @click="addExperience"
                class="w-20 h-10 flex items-center justify-center rounded bg-customButton text-white hover:bg-dark-slate"
              >
                Add +
              </button>
            </div>

            <!-- Existing Experiences -->
            <div
              v-for="(exp, index) in resume.experience"
              :key="index"
              class="relative border p-3 rounded space-y-3"
            >
              <button
                type="button"
                @click="removeExperience(index)"
                class="absolute top-2 right-2 text-gray-500 hover:text-red-500"
              >
                ✕
              </button>

              <p>
                <strong>{{ exp.jobTitle }}</strong>
              </p>
              <p>{{ exp.companyName }}</p>
              <p>{{ exp.companyAddress }}</p>
              <p>
                {{ new Date(exp.startYear).getFullYear() }} -
                {{ new Date(exp.endYear).getFullYear() }}
              </p>
            </div>
          </div>

          <div class="border rounded p-4 space-y-4 relative">
            <!-- Header with Plus Button -->
            <div class="flex justify-between items-center">
              <label class="text-lg font-semibold">Education</label>
              <button
                type="button"
                @click="showNewEducationForm = !showNewEducationForm"
                class="w-8 h-8 flex items-center justify-center rounded-full bg-customButton text-white hover:bg-dark-slate"
              >
                +
              </button>
            </div>

            <!-- New Education Form -->
            <div
              v-if="showNewEducationForm"
              class="border p-3 rounded space-y-3 mt-3"
            >
              <!-- Education Level -->
              <div>
                <label class="block font-medium mb-1">Education Level <span class="text-red-500">*</span></label>
                <select
                  v-model="newEducation.educationLevel"
                  class="input-field border rounded w-full p-2"
                >
                  <option value="" disabled>Select level</option>
                  <option>Elementary</option>
                  <option>High School</option>
                  <option>Senior High School</option>
                  <option>Bachelors Degree</option>
                  <option>Masters Degree</option>
                  <option>Doctorate</option>
                </select>
              </div>
              <div>
                <label class="block font-medium mb-1">Program</label>
                <input
                  v-model="newEducation.program"
                  type="text"
                  placeholder="Enter program"
                  class="input-field border rounded w-full p-2 disabled:bg-gray-200"
                  :disabled="
                    newEducation.educationLevel === 'Elementary' ||
                    newEducation.educationLevel === 'High School' ||
                    newEducation.educationLevel === 'Senior High School'
                  "
                  maxlength="100"
                />
                <p v-if="newEducation.program.length >= 100" class="text-xs text-red-500 mt-1">
                  Maximum character limit (100) reached
                </p>
                <p v-else-if="newEducation.program.length > 0" class="text-xs text-gray-500 mt-1">
                  {{ 100 - newEducation.program.length }} characters remaining
                </p>
              </div>
              <!-- Major -->
              <div>
                <label class="block font-medium mb-1">Major</label>
                <input
                  v-model="newEducation.major"
                  type="text"
                  placeholder="Enter major"
                  class="input-field border rounded w-full p-2 disabled:bg-gray-200"
                  :disabled="
                    newEducation.educationLevel === 'Elementary' ||
                    newEducation.educationLevel === 'High School' ||
                    newEducation.educationLevel === 'Senior High School'
                  "
                  maxlength="100"
                />
                <p v-if="newEducation.major.length >= 100" class="text-xs text-red-500 mt-1">
                  Maximum character limit (100) reached
                </p>
                <p v-else-if="newEducation.major.length > 0" class="text-xs text-gray-500 mt-1">
                  {{ 100 - newEducation.major.length }} characters remaining
                </p>
              </div>
              <div>
                <label class="block font-medium mb-1">Strand</label>
                <input
                  v-model="newEducation.strand"
                  type="text"
                  placeholder="Enter strand"
                  class="input-field border rounded w-full p-2 disabled:bg-gray-200"
                  :disabled="
                    newEducation.educationLevel === 'Elementary' ||
                    newEducation.educationLevel === 'High School' ||
                    newEducation.educationLevel === 'Bachelors Degree' ||
                    newEducation.educationLevel === 'Masters Degree' ||
                    newEducation.educationLevel === 'Doctorate'
                  "
                  maxlength="100"
                />
                <p v-if="newEducation.strand.length >= 100" class="text-xs text-red-500 mt-1">
                  Maximum character limit (100) reached
                </p>
                <p v-else-if="newEducation.strand.length > 0" class="text-xs text-gray-500 mt-1">
                  {{ 100 - newEducation.strand.length }} characters remaining
                </p>
              </div>
              <!-- Institution Name -->
              <div>
                <label class="block font-medium mb-1">Institution Name <span class="text-red-500">*</span></label>
                <input
                  v-model="newEducation.institutionName"
                  type="text"
                  placeholder="Enter institution name*"
                  class="input-field border rounded w-full p-2"
                  maxlength="100"
                />
                <p v-if="newEducation.institutionName.length >= 100" class="text-xs text-red-500 mt-1">
                  Maximum character limit (100) reached
                </p>
                <p v-else-if="newEducation.institutionName.length > 0" class="text-xs text-gray-500 mt-1">
                  {{ 100 - newEducation.institutionName.length }} characters remaining
                </p>
              </div>

              <!-- Institution Address -->
              <div>
                <label class="block font-medium mb-1">Institution Address <span class="text-red-500">*</span></label>
                <input
                  v-model="newEducation.institutionAddress"
                  type="text"
                  placeholder="Enter address*"
                  class="input-field border rounded w-full p-2"
                  maxlength="100"
                />
                <p v-if="newEducation.institutionAddress.length >= 100" class="text-xs text-red-500 mt-1">
                  Maximum character limit (100) reached
                </p>
                <p v-else-if="newEducation.institutionAddress.length > 0" class="text-xs text-gray-500 mt-1">
                  {{ 100 - newEducation.institutionAddress.length }} characters remaining
                </p>
              </div>

              <!-- Graduation Year -->
              <div>
                <label class="block font-medium mb-1">Graduation Year <span class="text-red-500">*</span></label>
                <input
                  v-model.number="newEducation.graduationYear"
                  type="number"
                  min="1900"
                  :max="new Date().getFullYear()"
                  placeholder="e.g. 2025*"
                  class="input-field border rounded w-full p-2"
                />
              </div>

              <!-- Add Button -->
              <button
                type="button"
                @click="addEducation"
                class="w-20 h-10 flex items-center justify-center rounded bg-customButton text-white hover:bg-dark-slate"
              >
                Add +
              </button>
            </div>

            <!-- Existing Education Items -->
            <div
              v-for="(edu, index) in resume.education"
              :key="index"
              class="relative border p-3 rounded space-y-3"
            >
              <!-- Delete Button -->
              <button
                type="button"
                @click="removeEducation(index)"
                class="absolute top-2 right-2 text-gray-500 hover:text-red-500"
              >
                ✕
              </button>

              <p class="font-semibold text-dark-slate">
                {{ edu.educationLevel }}
              </p>
              <p v-if="edu.program">Program: {{ edu.program }}</p>
              <p v-if="edu.major">Major: {{ edu.major }}</p>
              <p v-if="edu.strand">Strand: {{ edu.strand }}</p>
              <p class="font-semibold">{{ edu.institutionName }}</p>
              <p v-if="edu.institutionAddress">{{ edu.institutionAddress }}</p>
              <p v-if="edu.graduationYear">Graduated {{ edu.graduationYear }}</p>
            </div>
          </div>

          <!-- Skills -->
          <div class="border rounded p-4 space-y-3">
            <label class="text-lg font-semibold">Skills</label>

            <!-- Skill Search and Add -->
            <div class="relative">
              <div class="flex gap-2">
                <div class="flex-1 relative">
                  <input
                    v-model="skillSearchQuery"
                    type="text"
                    placeholder="Type to search skills or add custom..."
                    class="input-field w-full border rounded p-2"
                    @input="handleSkillInput"
                    @focus="handleSkillFocus"
                    @blur="handleSkillBlur"
                    @keyup.enter="addCurrentSkill"
                    @keyup.escape="hideSuggestions"
                    maxlength="100"
                  />

                  <!-- Skills Suggestions Dropdown -->
                  <div
                    v-if="showSuggestions"
                    class="absolute top-full left-0 right-0 bg-white border border-gray-200 rounded-b-md shadow-lg z-10 max-h-48 overflow-y-auto"
                  >
                    <!-- Filtered search results -->
                    <div v-if="filteredSuggestions.length > 0">
                      <div
                        v-for="skill in filteredSuggestions"
                        :key="'filtered-' + skill"
                        @mousedown="selectSkill(skill)"
                        class="px-3 py-2 hover:bg-gray-100 cursor-pointer text-sm"
                      >
                        {{ skill }}
                      </div>
                    </div>

                    <!-- Popular skills when no search -->
                    <div v-else-if="!skillSearchQuery.trim() && popularSkills.length > 0">
                      <div class="px-3 py-2 text-xs text-gray-500 border-b border-gray-100">
                        Popular skills from organizations:
                      </div>
                      <div
                        v-for="(skill, index) in popularSkills.slice(0, 8)"
                        :key="'popular-' + index"
                        @mousedown="selectSkill(skill.tagName)"
                        class="px-3 py-2 hover:bg-gray-100 cursor-pointer text-sm"
                      >
                        {{ skill.tagName }}
                      </div>
                    </div>

                    <!-- Add custom skill option -->
                    <div v-if="skillSearchQuery.trim() && filteredSuggestions.length === 0" class="px-3 py-2 text-sm text-blue-600 border-t border-gray-100">
                      ➕ Press Enter or click Add to add "{{ skillSearchQuery }}" as custom skill
                    </div>
                  </div>
                </div>
                <button
                  type="button"
                  @click="addCurrentSkill"
                  :disabled="!skillSearchQuery.trim()"
                  class="px-4 py-2 bg-customButton text-white rounded hover:bg-dark-slate disabled:bg-gray-300 disabled:cursor-not-allowed whitespace-nowrap"
                >
                  Add Skill
                </button>
              </div>
            </div>

            <!-- Selected Skills Tags -->
            <div class="flex flex-wrap gap-2 mt-3" v-if="resume.skills.length">
              <span
                v-for="(skill, index) in resume.skills"
                :key="index"
                class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full flex items-center gap-2 text-sm"
              >
                {{ skill.skillName || skill }}
                <button
                  type="button"
                  @click="removeSkill(index)"
                  class="text-blue-500 hover:text-red-500 text-sm"
                >
                  ✕
                </button>
              </span>
            </div>
          </div>

          <div class="border rounded p-4 space-y-3">
            <h3 class="text-lg font-semibold">Certificates</h3>

            <ul v-if="selectedCertificates.length">
              <li
                v-for="cert in selectedCertificates"
                :key="cert.certificationID"
                class="flex justify-between items-center bg-gray-50 px-3 py-2 rounded-lg hover:bg-gray-100 transition"
              >
                <span class="text-gray-800">{{ cert.certificationName }}</span>
              </li>
            </ul>

            <p v-else class="text-gray-500 text-sm">
              No certificates selected for your resume.
            </p>
          </div>

          <!-- Save Button -->
          <div class="flex gap-4 justify-end">
            <button
              type="button"
              @click="generateAndOpenPdf"
              class="px-6 py-3 bg-customButton text-white rounded-xl hover:bg-dark-slate transition"
            >
              Download Resume
            </button>
          </div>
        </form>
        <!-- PDF Preview Modal -->
        <div
          v-if="isModalOpen"
          class="fixed inset-0 flex items-center justify-center bg-opacity-50 z-50"
        >
          <div
            class="bg-white rounded-lg shadow-lg w-full max-w-[min(96vw,900px)] relative"
          >
            <!-- Close Button -->
            <button
              @click="closeModal"
              class="absolute top-2 right-2 text-gray-500 hover:text-gray-700"
            >
              ✕
            </button>
            <div class="p-4">
              <h2 class="text-xl font-semibold mb-4">Resume Preview</h2>
              <!-- PDF Preview -->
              <iframe
                v-if="pdfUrl"
                :src="pdfUrl"
                class="w-full h-[500px] border"
                frameborder="0"
              ></iframe>
              <!-- Download Button -->
              <div class="mt-4 flex justify-end">
                <a
                  v-if="pdfUrl"
                  :href="pdfUrl"
                  download="resume.pdf"
                  class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700"
                >
                  Download PDF
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="toast toast-end toast-top z-50">
      <div
        v-for="toast in toasts"
        :key="toast.id"
        class="alert"
        :class="{
          'alert-info': toast.type === 'info',
          'alert-success': toast.type === 'success',
          'alert-accent': toast.type === 'accent',
        }"
      >
        {{ toast.message }}
      </div>
    </div>
  </div>
</template>
