<script setup>
import { reactive, ref, onMounted, onActivated } from "vue";
import jsPDF from "jspdf";
import axios from "axios";
import { useRouter } from "vue-router";
import { useActivityStore } from "@/stores/activityStore";

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

  const startDate = toDateFromYear(newExperience.startYear);
  const endDate = toDateFromYear(newExperience.endYear);

  if (!startDate || !endDate) {
    showToast("Please provide valid start and end years.");
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
        ...newExperience,
        startYear: startDate,
        endYear: endDate,
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

async function addSkill() {
  if (!newSkill.value.trim()) return;

  try {
    const token = localStorage.getItem("token");
    if (!resume.resumeID) {
      showToast("Please save your resume first before adding skills.");
      return;
    }

    const { data } = await axios.post(
      import.meta.env.VITE_API_BASE_URL + "/skills",
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
  doc.text(fullName, pageWidth / 2, y, { align: "center" });
  y += 8;

  doc.setFont("times", "regular");
  doc.setFontSize(10);
  // Build contact line in order: email, url, phone number, address
  const contactParts = [];
  if (form.emailAddress) contactParts.push(form.emailAddress);
  if (resume.url) contactParts.push(resume.url);
  if (form.phoneNumber) contactParts.push(form.phoneNumber);
  if (form.address) contactParts.push(form.address);
  const contactLine = contactParts.join(" • ");
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
      if (y > pageHeight - margin) {
        doc.addPage();
        y = margin;
      }

      const gradYear = edu.graduationYear?.toString() || "";
      const programLabel =
        edu.program ||
        (edu.educationLevel ? `${edu.educationLevel}` : "") ||
        "";
      const institutionAddress = edu.institutionAddress || "";

      doc.setFont("times", "bold");
      doc.text(`${edu.institutionName || ""}`, margin, y);
      doc.text(gradYear, pageWidth - margin, y, { align: "right" });
      y += 6;

      doc.setFont("times", "regular");
      if (programLabel || institutionAddress) {
        if (programLabel) {
          doc.text(programLabel, margin, y);
        }
        if (institutionAddress) {
          const addressLines = doc.splitTextToSize(
            institutionAddress,
            pageWidth / 2
          );
          doc.text(addressLines[0], pageWidth - margin, y, {
            align: "right",
          });
          for (let i = 1; i < addressLines.length; i++) {
            y += 6;
            doc.text(addressLines[i], pageWidth - margin, y, {
              align: "right",
            });
          }
        }
        y += 6;
      }

      if (edu.major) {
        doc.text(edu.major, margin, y);
        y += 6;
      }
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
  await activityStore.fetchCounts();
  await fetchSelectedCertificates(); // Ensure certificates are loaded
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
                />
              </div>
              <!-- Major -->
              <div>
                <label class="block font-medium mb-1">Major (Optional)</label>
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
                />
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
                />
              </div>
              <!-- Institution Name -->
              <div>
                <label class="block font-medium mb-1">Institution Name</label>
                <input
                  v-model="newEducation.institutionName"
                  type="text"
                  placeholder="Enter institution name"
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
                  v-model.number="newEducation.graduationYear"
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
