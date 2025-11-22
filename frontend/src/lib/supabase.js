import { createClient } from "@supabase/supabase-js";

const supabaseUrl = import.meta.env.VITE_SUPABASE_URL || "https://hmevengvfponcwslnyye.supabase.co";
const supabaseKey =
  import.meta.env.VITE_SUPABASE_SERVICE_ROLE_KEY ||
  import.meta.env.VITE_SUPABASE_ANON_KEY ||
  import.meta.env.VITE_SUPABASE_KEY;

export const supabase = createClient(supabaseUrl, supabaseKey);

const DEFAULT_BUCKET = "Requirements"; // same bucket for both

/**
 * Upload PDF file to Supabase Storage
 * @param {File} file - The PDF file to upload
 * @param {string} bucketName - The bucket name (default: 'Requirements')
 * @param {string} folder - The folder inside the bucket ('requirement_directory' or 'certificate_directory')
 * @returns {Promise<string|null>} - Returns the file path if successful, null otherwise
 */
export async function uploadPDF(file, bucketName = DEFAULT_BUCKET, folder = "requirement_directory") {
  if (!file) return null;

  if (file.type !== "application/pdf") {
    console.error("Only PDF files are allowed.");
    return null;
  }

  const fileName = `${folder}/${Date.now()}_${file.name}`;
  console.log(`Uploading file to ${bucketName}/${folder}:`, fileName);

  try {
    const { data, error } = await supabase.storage.from(bucketName).upload(fileName, file, {
      cacheControl: "3600",
      upsert: false,
    });

    if (error) {
      console.error(`Supabase upload error (${bucketName}/${folder}):`, error);
      return null;
    }

    return fileName;
  } catch (error) {
    console.error(`Upload error (${bucketName}/${folder}):`, error);
    return null;
  }
}

/**
 * Upload PDF to Requirements folder
 */
export async function uploadRequirement(file, bucketName = DEFAULT_BUCKET) {
  return uploadPDF(file, bucketName, "requirement_directory");
}

/**
 * Upload PDF to Certificates folder
 */
export async function uploadCertificate(file, bucketName = DEFAULT_BUCKET) {
  const filePath = await uploadPDF(file, bucketName, "certificate_directory");
  if (!filePath) return null;

  const { data } = supabase.storage.from(bucketName).getPublicUrl(filePath);
  return data?.publicUrl || null;
}

/**
 * Get public URL for a PDF file
 * @param {string} fileName - The file path in Supabase
 * @param {string} bucketName - The bucket name (default: 'Requirements')
 * @returns {string} - Public URL to the file
 */
export function getPDFUrl(fileName, bucketName = DEFAULT_BUCKET) {
  const { data } = supabase.storage.from(bucketName).getPublicUrl(fileName);
  return data.publicUrl;
}