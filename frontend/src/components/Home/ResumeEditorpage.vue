<script setup>
import { reactive, ref, onMounted } from "vue";
import jsPDF from "jspdf";
import axios from "axios";
import { useRouter } from "vue-router";

const isModalOpen = ref(false);
const pdfUrl = ref(null);
const newSkill = ref("");
const router = useRouter();
const userName = ref("");
const upcomingCount = ref(0);
const completedCount = ref(0);

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
  major: "",
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

// Fetch TrainingCounter
async function fetchTrainingCounters() {
  try {
    const token = localStorage.getItem("token");
    if (!token) return;

    const response = await axios.get(
      import.meta.env.VITE_API_BASE_URL + "/registrations",
      { headers: { Authorization: `Bearer ${token}` } }
    );

    const trainings = response.data || [];

    let newUpcoming = 0;
    let newCompleted = 0;
    const now = new Date().getTime();

    trainings.forEach((r) => {
      const statusLower = (r.registrationStatus || "").toLowerCase();
      const endTimePassed = r.end_time
        ? now > new Date(r.end_time).getTime()
        : false;

      // Mark as completed if end_time passed or status is completed
      if (statusLower === "completed" || endTimePassed) {
        newCompleted++;
      } else if (["upcoming", "registered"].includes(statusLower)) {
        newUpcoming++;
      }
    });

    upcomingCount.value = newUpcoming;
    completedCount.value = newCompleted;
  } catch (error) {
    console.error("❌ Error fetching training counters:", error);
  }
}

// --- Save Resume ---
async function saveResume() {
  try {
    const token = localStorage.getItem("token");
    const response = await axios.post(
      import.meta.env.VITE_API_BASE_URL +"/resume",
      {
        summary: resume.summary,
        professionalLink: resume.url,
      },
      { headers: { Authorization: `Bearer ${token}` } }
    );

    resume.resumeID = response.data.resumeID;
    alert("Resume saved successfully!");
  } catch (error) {
    console.error("Error saving resume:", error.response?.data || error);
    alert("Failed to save resume.");
  }
}

// --- Load Resume ---
async function loadResume() {
  try {
    const token = localStorage.getItem("token");
    const { data } = await axios.get(import.meta.env.VITE_API_BASE_URL +"/resume", {
      headers: { Authorization: `Bearer ${token}` },
    });

    if (data) {
      resume.summary = data.summary || "";
      resume.url = data.professionalLink || "";
      resume.resumeID = data.resumeID;
    }

    const { data: expData } = await axios.get(
      import.meta.env.VITE_API_BASE_URL +"/experiences",
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
    await axios.delete(import.meta.env.VITE_API_BASE_URL +"/resume", {
      headers: { Authorization: `Bearer ${token}` },
    });

    resume.summary = "";
    resume.url = "";
    alert("Resume deleted successfully!");
  } catch (error) {
    console.error("Error deleting resume:", error.response?.data || error);
    alert("Failed to delete resume.");
  }
}

// --- Education ---
async function addEducation() {
  try {
    const token = localStorage.getItem("token");

    if (!resume.resumeID) {
      const resumeRes = await axios.post(
        import.meta.env.VITE_API_BASE_URL +"/resume",
        {
          summary: resume.summary || "",
          professionalLink: resume.url || "",
        },
        { headers: { Authorization: `Bearer ${token}` } }
      );
      resume.resumeID = resumeRes.data.resumeID;
    }

    const { data } = await axios.post(
      import.meta.env.VITE_API_BASE_URL +"/education",
      { ...newEducation, resumeID: resume.resumeID },
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
    alert("Education added successfully!");
  } catch (error) {
    console.error("Error adding education:", error.response?.data || error);
    alert("Failed to add education.");
  }
}

async function loadEducation() {
  try {
    const token = localStorage.getItem("token");
    const resumeID = resume.resumeID || localStorage.getItem("resumeID");

    const { data } = await axios.get(
      import.meta.env.VITE_API_BASE_URL +`/education?resumeID=${resumeID}`,
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
        import.meta.env.VITE_API_BASE_URL +`/education/${edu.educationID}`,
        {
          headers: { Authorization: `Bearer ${token}` },
        }
      );
    }
    resume.education.splice(index, 1);
  } catch (error) {
    console.error("Error deleting education:", error.response?.data || error);
    alert("Failed to delete education.");
  }
}

// --- Experience ---
async function addExperience() {
  if (
    !newExperience.jobTitle.trim() ||
    !newExperience.companyName.trim() ||
    !newExperience.companyAddress.trim()
  ) {
    alert("Please fill out all fields before adding experience.");
    return;
  }

  try {
    const token = localStorage.getItem("token");

    if (!resume.resumeID) {
      const resumeRes = await axios.post(
        import.meta.env.VITE_API_BASE_URL +"/resume",
        {
          summary: resume.summary || "",
          professionalLink: resume.url || "",
        },
        { headers: { Authorization: `Bearer ${token}` } }
      );
      resume.resumeID = resumeRes.data.resumeID;
    }

    const { data } = await axios.post(
      import.meta.env.VITE_API_BASE_URL +"/experiences",
      {
        ...newExperience,
        startYear: `${newExperience.startYear}-01-01`,
        endYear: `${newExperience.endYear}-01-01`,
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
    alert("Experience added successfully!");
  } catch (error) {
    console.error("Error adding experience:", error.response?.data || error);
    alert("Failed to add experience.");
  }
}

async function removeExperience(index) {
  try {
    const token = localStorage.getItem("token");
    const exp = resume.experience[index];
    if (exp.experienceID) {
      await axios.delete(
        import.meta.env.VITE_API_BASE_URL +`/experiences/${exp.experienceID}`,
        {
          headers: { Authorization: `Bearer ${token}` },
        }
      );
    }
    resume.experience.splice(index, 1);
  } catch (error) {
    console.error("Error deleting experience:", error.response?.data || error);
    alert("Failed to delete experience.");
  }
}

// --- Skills ---
async function loadSkills(resumeID) {
  try {
    const token = localStorage.getItem("token");
    const { data } = await axios.get(
      import.meta.env.VITE_API_BASE_URL +`/skills/${resumeID}`,
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

async function addSkill() {
  if (!newSkill.value.trim()) return;

  try {
    const token = localStorage.getItem("token");
    if (!resume.resumeID) {
      alert("Please save your resume first before adding skills.");
      return;
    }

    const { data } = await axios.post(
      import.meta.env.VITE_API_BASE_URL +"/skills",
      { skillName: newSkill.value.trim(), resumeID: resume.resumeID },
      { headers: { Authorization: `Bearer ${token}` } }
    );

    resume.skills.push(data);
    newSkill.value = "";
  } catch (error) {
    console.error("Error adding skill:", error.response?.data || error);
  }
}

async function removeSkill(index) {
  try {
    const token = localStorage.getItem("token");
    const skill = resume.skills[index];
    if (skill.skillID) {
      await axios.delete(import.meta.env.VITE_API_BASE_URL +`/skills/${skill.skillID}`, {
        headers: { Authorization: `Bearer ${token}` },
      });
    }
    resume.skills.splice(index, 1);
  } catch (error) {
    console.error("Error removing skill:", error.response?.data || error);
  }
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

  function getYearOnly(dateStr) {
    if (!dateStr) return "";
    const year = new Date(dateStr).getFullYear();
    return isNaN(year) ? "" : year.toString();
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
  doc.text(
    `${form.firstName} ${form.middleName} ${form.lastName}`,
    pageWidth / 2,
    y,
    { align: "center" }
  );
  y += 8;

  doc.setFont("times", "regular");
  doc.setFontSize(10);
  const contactLine = `${form.phoneNumber} • ${form.emailAddress} • ${resume.url}`;
  doc.text(contactLine, pageWidth / 2, y, { align: "center" });
  y += 12;

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
      const start = getYearOnly(exp.startYear);
      const end = exp.endYear ? getYearOnly(exp.endYear) : "Present";
      doc.setFont("times", "bold");
      doc.text(exp.jobTitle || "", margin, y);
      doc.setFont("times", "bold");
      doc.text(`${start} – ${end}`, pageWidth - margin, y, { align: "right" });
      y += 6;
      doc.setFont("times", "italic");
      doc.text(`${exp.companyName || ""}`, margin, y);
      y += 6;
    });
  }

  // Education
    if (resume.education.length) {
      y = sectionHeader("Education", margin, y, pageWidth, margin);
      resume.education.forEach((edu) => {
        // FIX: Assuming the backend (EducationController) now returns
        // graduationYear as a 4-digit number (e.g., 2024), we use it directly.
        // Removed the unnecessary getYearOnly() call.
        const gradYear = edu.graduationYear; 
        
        doc.setFont("times", "bold");
        doc.text(`${edu.institutionName || ""}`, margin, y);
        doc.text(gradYear, pageWidth - margin, y, { align: "right" });
        y += 6;
        doc.setFont("times", "regular");
        doc.text(`${edu.educationLevel} in ${edu.major}`, margin, y);
        y += 6;
      });
    }

  // Skills
  if (resume.skills.length) {
    y = sectionHeader("Skills", margin, y, pageWidth, margin);
    y = addWrappedText(
      resume.skills.map((s) => s.skillName || s).join(" • "),
      margin,
      y,
      pageWidth - 2 * margin
    );
  }

  // Certificates
  if (resume.certificates && resume.certificates.length) {
    y = sectionHeader("Certificates", margin, y, pageWidth, margin);
    doc.setFont("times", "regular");
    doc.setFontSize(11);

    resume.certificates.forEach((cert) => {
      if (y > pageHeight - margin) {
        doc.addPage();
        y = margin;
      }

      // ✅ Add bullet point before certificate name
      const bullet = "•";
      const certName = cert.certificationName || cert.title || "";
      doc.text(`${bullet} ${certName}`, margin, y);
      y += 6;
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

// --- Autofill + Load Data ---
onMounted(async () => {
  const savedUser = localStorage.getItem("user");
  if (savedUser) {
    try {
      const user = JSON.parse(savedUser);
      userName.value = `${user.firstName} ${user.lastName}`;
      Object.assign(form, user);
    } catch {
      userName.value = "Guest";
    }
  }
  await loadResume();
  await fetchTrainingCounters();
});

const logout = () => {
  localStorage.removeItem("user");
  localStorage.removeItem("token");
  router.push({ name: "Login" });
};

const selectedCertificates = ref([]);

async function fetchSelectedCertificates() {
  const token = localStorage.getItem("token");
  const user = JSON.parse(localStorage.getItem("user"));
  if (!token || !user?.applicantID) return [];

  try {
    const response = await axios.get(
      import.meta.env.VITE_API_BASE_URL +`/certificates/${user.applicantID}/selected`,
      { headers: { Authorization: `Bearer ${token}` } }
    );

    // ✅ Update reactive state for the resume page
    selectedCertificates.value = response.data.filter(
      (cert) => cert.IsSelected === 1
    );

    // ✅ Also return it for PDF preview
    return selectedCertificates.value;
  } catch (error) {
    console.error("❌ Error fetching selected certificates:", error);
    selectedCertificates.value = [];
    return [];
  }
}

onMounted(fetchSelectedCertificates);
</script>

<template>
  <div class="min-h-screen p-3 rounded-lg font-poppins">
    <!--Large screen-->
    <div class="min-h-screen font-poppins lg:flex">
      <!-- Left Column -->
      <div
        class="w-full lg:w-1/4 bg-white rounded-lg shadow p-6 flex flex-col items-center hidden lg:flex"
      >
        <!-- Avatar -->
        <div class="w-24 h-24 rounded-full bg-white mb-4">
          <div class="avatar w-24 h-24 rounded-full bg-white mb-4">
            <img
              src="https://img.daisyui.com/images/profile/demo/yellingcat@192.webp"
              alt="User Avatar"
              class="w-full h-full object-cover rounded-full"
            />
          </div>
        </div>

        <!-- Name -->
        <h2 class="text-xl font-semibold mb-6">{{ userName }}</h2>
        <div
          class="w-full flex items-center justify-center gap-6 mb-6 relative"
        >
          <!-- Upcoming -->
          <div class="relative">
            <div
              class="flex items-center justify-center bg-gray-100 rounded-full px-6 py-2"
            >
              <span class="font-semibold text-gray-700">Upcoming</span>
            </div>
            <!-- Floating Bubble -->
            <span
              class="absolute -top-2 -right-2 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white bg-customButton rounded-full"
            >
              {{ upcomingCount }}
            </span>
          </div>

          <!-- Completed -->
          <div class="relative">
            <div
              class="flex items-center justify-center bg-gray-100 rounded-full px-6 py-2"
            >
              <span class="font-semibold text-gray-700">Completed</span>
            </div>
            <!-- Floating Bubble -->
            <span
              class="absolute -top-2 -right-2 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white bg-customButton rounded-full"
            >
              {{ completedCount }}
            </span>
          </div>
        </div>

        <!-- Buttons -->
        <div class="w-full flex flex-col gap-3">
          <button
            class="bg-customButton text-white py-2 px-10 rounded-md hover:bg-dark-slate flex items-center justify-start gap-2"
            @click="$router.push({ name: 'ResumeEditorpage' })"
          >
            <svg
              class="size-6 flex-shrink-0"
              viewBox="0 0 34 34"
              fill="none"
              xmlns="http://www.w3.org/2000/svg"
            >
              <path
                d="M19.8333 3.21521V9.06681C19.8333 9.86022 19.8333 10.2569 19.9877 10.56C20.1235 10.8265 20.3402 11.0432 20.6068 11.1791C20.9098 11.3335 21.3066 11.3335 22.1 11.3335H27.9516M22.6666 18.4167H11.3333M22.6666 24.0834H11.3333M14.1666 12.75H11.3333M19.8333 2.83337H12.4666C10.0864 2.83337 8.89629 2.83337 7.98717 3.2966C7.18748 3.70406 6.53731 4.35423 6.12985 5.15391C5.66663 6.06304 5.66663 7.25315 5.66663 9.63337V24.3667C5.66663 26.7469 5.66663 27.937 6.12985 28.8462C6.53731 29.6459 7.18748 30.296 7.98717 30.7035C8.89629 31.1667 10.0864 31.1667 12.4666 31.1667H21.5333C23.9135 31.1667 25.1036 31.1667 26.0128 30.7035C26.8124 30.296 27.4626 29.6459 27.8701 28.8462C28.3333 27.937 28.3333 26.7469 28.3333 24.3667V11.3334L19.8333 2.83337Z"
                stroke="white"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
              />
            </svg>
            <span>Resume</span>
          </button>
          <button
            class="bg-customButton text-white py-2 px-10 rounded-md hover:bg-dark-slate flex items-center justify-start gap-2"
            @click="$router.push({ name: 'Certificatespage' })"
          >
            <svg
              class="size-6 flex-shrink-0"
              viewBox="0 0 35 35"
              fill="none"
              xmlns="http://www.w3.org/2000/svg"
            >
              <path
                d="M9.47917 29.1666H7.29167C5.68084 29.1666 4.375 27.8608 4.375 26.25V5.83329C4.375 4.22246 5.68084 2.91663 7.29167 2.91663H27.7083C29.3192 2.91663 30.625 4.22246 30.625 5.83329V26.25C30.625 27.8608 29.3192 29.1666 27.7083 29.1666H25.5208M17.5 27.7083C19.9162 27.7083 21.875 25.7495 21.875 23.3333C21.875 20.917 19.9162 18.9583 17.5 18.9583C15.0838 18.9583 13.125 20.917 13.125 23.3333C13.125 25.7495 15.0838 27.7083 17.5 27.7083ZM17.5 27.7083L17.5313 27.708L12.8751 32.3641L8.75036 28.2393L13.1537 23.836M17.5 27.7083L22.1562 32.3641L26.281 28.2393L21.8777 23.836M13.125 8.74996H21.875M10.2083 13.8541H24.7917"
                stroke="white"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
              />
            </svg>
            <span>Certificates</span>
          </button>

          <button
            class="bg-customButton text-white py-2 px-10 rounded-md hover:bg-dark-slate flex items-center justify-start gap-2"
            @click="$router.push({ name: 'Bookmarkpage' })"
          >
            <svg
              class="size-6 flex-shrink-0"
              viewBox="0 0 31 30"
              fill="none"
              xmlns="http://www.w3.org/2000/svg"
            >
              <path
                d="M7.75 3.75V30L17.4375 20.625L27.125 30V3.75H7.75ZM23.25 0H3.875V26.25L5.8125 24.375V1.875H23.25V0Z"
                fill="white"
              />
            </svg>

            <span>Bookmark</span>
          </button>
          <div class="divider"></div>
          <button
            class="bg-customButton text-white py-2 px-10 rounded-md hover:bg-dark-slate flex items-center justify-start gap-2"
            @click="$router.push({ name: 'UpdateDeletepage' })"
          >
            <svg
              class="size-6 flex-shrink-0"
              viewBox="0 0 24 24"
              fill="none"
              xmlns="http://www.w3.org/2000/svg"
            >
              <path
                d="M20.1 9.21994C18.29 9.21994 17.55 7.93994 18.45 6.36994C18.97 5.45994 18.66 4.29994 17.75 3.77994L16.02 2.78994C15.23 2.31994 14.21 2.59994 13.74 3.38994L13.63 3.57994C12.73 5.14994 11.25 5.14994 10.34 3.57994L10.23 3.38994C9.78 2.59994 8.76 2.31994 7.97 2.78994L6.24 3.77994C5.33 4.29994 5.02 5.46994 5.54 6.37994C6.45 7.93994 5.71 9.21994 3.9 9.21994C2.86 9.21994 2 10.0699 2 11.1199V12.8799C2 13.9199 2.85 14.7799 3.9 14.7799C5.71 14.7799 6.45 16.0599 5.54 17.6299C5.02 18.5399 5.33 19.6999 6.24 20.2199L7.97 21.2099C8.76 21.6799 9.78 21.3999 10.25 20.6099L10.36 20.4199C11.26 18.8499 12.74 18.8499 13.65 20.4199L13.76 20.6099C14.23 21.3999 15.25 21.6799 16.04 21.2099L17.77 20.2199C18.68 19.6999 18.99 18.5299 18.47 17.6299C17.56 16.0599 18.3 14.7799 20.11 14.7799C21.15 14.7799 22.01 13.9299 22.01 12.8799V11.1199C22 10.0799 21.15 9.21994 20.1 9.21994ZM12 15.2499C10.21 15.2499 8.75 13.7899 8.75 11.9999C8.75 10.2099 10.21 8.74994 12 8.74994C13.79 8.74994 15.25 10.2099 15.25 11.9999C15.25 13.7899 13.79 15.2499 12 15.2499Z"
                fill="white"
              />
            </svg>

            <span>Account Setting</span>
          </button>
          <button
            class="bg-customButton text-white py-2 px-10 rounded-md hover:bg-dark-slate flex items-center justify-start gap-2"
            @click="logout"
          >
            <svg
              class="size-6 flex-shrink-0"
              viewBox="0 0 24 24"
              fill="none"
              xmlns="http://www.w3.org/2000/svg"
            >
              <path
                d="M15.24 22.27H15.11C10.67 22.27 8.53002 20.52 8.16002 16.6C8.12002 16.19 8.42002 15.82 8.84002 15.78C9.24002 15.74 9.62002 16.05 9.66002 16.46C9.95002 19.6 11.43 20.77 15.12 20.77H15.25C19.32 20.77 20.76 19.33 20.76 15.26V8.73998C20.76 4.66998 19.32 3.22998 15.25 3.22998H15.12C11.41 3.22998 9.93002 4.41998 9.66002 7.61998C9.61002 8.02998 9.26002 8.33998 8.84002 8.29998C8.42002 8.26998 8.12001 7.89998 8.15001 7.48998C8.49001 3.50998 10.64 1.72998 15.11 1.72998H15.24C20.15 1.72998 22.25 3.82998 22.25 8.73998V15.26C22.25 20.17 20.15 22.27 15.24 22.27Z"
                fill="white"
              />
              <path
                d="M15.0001 12.75H3.62012C3.21012 12.75 2.87012 12.41 2.87012 12C2.87012 11.59 3.21012 11.25 3.62012 11.25H15.0001C15.4101 11.25 15.7501 11.59 15.7501 12C15.7501 12.41 15.4101 12.75 15.0001 12.75Z"
                fill="white"
              />
              <path
                d="M5.84994 16.1C5.65994 16.1 5.46994 16.03 5.31994 15.88L1.96994 12.53C1.67994 12.24 1.67994 11.76 1.96994 11.47L5.31994 8.12003C5.60994 7.83003 6.08994 7.83003 6.37994 8.12003C6.66994 8.41003 6.66994 8.89003 6.37994 9.18003L3.55994 12L6.37994 14.82C6.66994 15.11 6.66994 15.59 6.37994 15.88C6.23994 16.03 6.03994 16.1 5.84994 16.1Z"
                fill="white"
              />
            </svg>

            <span>Logout</span>
          </button>
        </div>
      </div>

      <div class="w-full lg:w-3/4 lg:pl-6 flex flex-col gap-6">
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
              />
              <input
                v-model="form.emailAddress"
                type="email"
                placeholder="Email"
                class="input-field border rounded p-2"
              />
              <input
                v-model="form.middleName"
                type="text"
                placeholder="Middle Name"
                class="input-field border rounded p-2"
              />
              <input
                v-model="form.phoneNumber"
                type="text"
                placeholder="Mobile Number"
                class="input-field border rounded p-2"
              />
              <input
                v-model="form.lastName"
                type="text"
                placeholder="Last Name"
                class="input-field border rounded p-2"
              />
              <input
                v-model="form.address"
                type="text"
                placeholder="Address"
                class="input-field border rounded p-2"
              />
            </div>

            <!-- URL -->
            <div class="border rounded p-4 space-y-4 relative">
              <h2 class="text-lg font-semibold">Professional Link</h2>
              <input
                type="url"
                placeholder="URL"
                class="input-field border rounded p-2 w-full"
                v-model="resume.url"
              />
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
                ></textarea>
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
                  <label class="block font-medium mb-1">Job Title</label>
                  <input
                    v-model="newExperience.jobTitle"
                    type="text"
                    placeholder="Enter job title"
                    class="input-field border rounded w-full p-2"
                  />
                </div>
                <div>
                  <label class="block font-medium mb-1">Company Name</label>
                  <input
                    v-model="newExperience.companyName"
                    type="text"
                    placeholder="Enter company name"
                    class="input-field border rounded w-full p-2"
                  />
                </div>
                <div>
                  <label class="block font-medium mb-1">Company Address</label>
                  <input
                    v-model="newExperience.companyAddress"
                    type="text"
                    placeholder="Enter company address"
                    class="input-field border rounded w-full p-2"
                  />
                </div>
                <div>
                  <label class="block font-medium mb-1">Start Year</label>
                  <input
                    v-model="newExperience.startYear"
                    type="number"
                    placeholder="e.g. 2020"
                    class="input-field border rounded w-full p-2"
                  />
                </div>
                <div>
                  <label class="block font-medium mb-1">End Year</label>
                  <input
                    v-model="newExperience.endYear"
                    type="number"
                    placeholder="e.g. 2022"
                    class="input-field border rounded w-full p-2"
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
                  <label class="block font-medium mb-1">Education Level</label>
                  <select
                    v-model="newEducation.educationLevel"
                    class="input-field border rounded w-full p-2"
                  >
                    <option value="" disabled>Select level</option>
                    <option>Elementary</option>
                    <option>High School</option>
                    <option>Senior High School</option>
                    <option>Bachelor's Degree</option>
                    <option>Master's Degree</option>
                    <option>Doctorate</option>
                    <option>Others</option>
                  </select>
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
                      newEducation.educationLevel === 'High School'
                    "
                  />
                </div>

                <!-- Institution Name -->
                <div>
                  <label class="block font-medium mb-1">Institution Name</label>
                  <input
                    v-model="newEducation.institutionName"
                    type="text"
                    placeholder="Enter school/university name"
                    class="input-field border rounded w-full p-2"
                  />
                </div>

                <!-- Institution Address -->
                <div>
                  <label class="block font-medium mb-1"
                    >Institution Address</label
                  >
                  <input
                    v-model="newEducation.institutionAddress"
                    type="text"
                    placeholder="Enter address"
                    class="input-field border rounded w-full p-2"
                  />
                </div>

                <!-- Graduation Year -->
                <div>
                  <label class="block font-medium mb-1">Graduation Year</label>
                  <input
                    v-model="newEducation.graduationYear"
                    type="number"
                    min="1900"
                    max="2099"
                    placeholder="e.g. 2025"
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

                <p>
                  <strong>{{ edu.educationLevel }}</strong>
                </p>
                <p v-if="edu.major">{{ edu.major }}</p>
                <p>{{ edu.institutionName }}</p>
                <p>{{ edu.institutionAddress }}</p>
                <p>{{ edu.graduationYear }}</p>
              </div>
            </div>

            <!-- Skills -->
            <div class="border rounded p-4 space-y-3">
              <label class="text-lg font-semibold">Skills</label>

              <div class="flex gap-2">
                <!-- 👇 use newSkill, not resume.skills -->
                <input
                  v-model="newSkill"
                  type="text"
                  placeholder="Type a skill"
                  class="input-field flex-1 border rounded p-2"
                  @keyup.enter="addSkill"
                />
                <button
                  type="button"
                  @click="addSkill"
                  class="w-8 h-8 flex items-center justify-center rounded-full bg-customButton text-white hover:bg-dark-slate"
                >
                  +
                </button>
              </div>

              <div class="flex flex-wrap gap-2 mt-2">
                <span
                  v-for="(skill, index) in resume.skills"
                  :key="index"
                  class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full flex items-center gap-2"
                >
                  {{ skill.skillName || skill }}
                  <button
                    type="button"
                    @click="removeSkill(index)"
                    class="text-blue-500 hover:text-red-500"
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
                  <span class="text-gray-800">{{
                    cert.certificationName
                  }}</span>
                </li>
              </ul>

              <p v-else class="text-gray-500 text-sm">
                No certificates selected for your resume.
              </p>
            </div>

            <!-- Save Button -->
            <div class="flex gap-4">
              <button
                type="button"
                @click="generateAndOpenPdf"
                class="w-1/2 py-3 bg-customButton text-white rounded-xl hover:bg-dark-slate transition"
              >
                Preview Resume
              </button>
              <button
                type="button"
                class="w-1/2 py-3 bg-customButton text-white rounded-xl hover:bg-dark-slate transition"
                @click="saveResume"
              >
                Save
              </button>
            </div>
          </form>
          <!-- PDF Preview Modal -->
          <div
            v-if="isModalOpen"
            class="fixed inset-0 flex items-center justify-center bg-opacity-50 z-50"
          >
            <div
              class="bg-white rounded-lg shadow-lg max-w-4xl w-full relative"
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
    </div>
  </div>
</template>
