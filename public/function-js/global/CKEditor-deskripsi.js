// Deklarasikan variabel global di scope window
window.editorInstance = null;

ClassicEditor.create(document.querySelector("#deskripsi"))
  .then((editor) => {
    window.editorInstance = editor; // Simpan instance ke global
    
    const MAX_CHARACTERS = 2000;
    const charCountElement = document.getElementById("charCount");

    const updateCharCount = () => {
      const text = editor.getData().replace(/<[^>]*>/g, "");
      const textLength = text.length;
      charCountElement.textContent = `${Math.min(textLength, MAX_CHARACTERS)} / ${MAX_CHARACTERS}`;
    };

    updateCharCount();
    editor.model.document.on("change:data", updateCharCount);
    
    // Hapus setData initial dari sini
  })
  .catch((error) => {
    console.error(error);
  });