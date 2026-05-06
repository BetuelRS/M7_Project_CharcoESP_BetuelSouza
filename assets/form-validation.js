// Form validation for all forms
document.addEventListener("DOMContentLoaded", function () {
  console.log("Form validation script loaded");
  
  // Validation configurations for different forms
  const formConfigs = {
    // Registration form (existing)
    ".auth-form": {
      fields: [
        "nome_completo",
        "username",
        "email",
        "password",
        "confirm_password",
      ],
      validations: {
        nome_completo: (value) => {
          if (!value.trim()) return "Nome completo é obrigatório";
          if (value.trim().length < 3)
            return "Nome completo deve ter pelo menos 3 caracteres";
          if (value.trim().length > 100)
            return "Nome completo deve ter no máximo 100 caracteres";
          return "";
        },
        username: (value) => {
          if (!value.trim()) return "Nome de utilizador é obrigatório";
          if (value.trim().length < 3)
            return "Nome de utilizador deve ter pelo menos 3 caracteres";
          if (value.trim().length > 20)
            return "Nome de utilizador deve ter no máximo 20 caracteres";
          if (!/^[a-zA-Z0-9_]+$/.test(value.trim()))
            return "Nome de utilizador só pode conter letras, números e underscore";
          return "";
        },
        email: (value) => {
          if (!value.trim()) return "Email é obrigatório";
          const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
          if (!emailRegex.test(value.trim())) return "Email inválido";
          return "";
        },
        password: (value) => {
          if (!value) return "Senha é obrigatória";
          if (value.length < 6) return "A senha deve ter pelo menos 6 caracteres";
          if (value.length > 50) return "A senha deve ter no máximo 50 caracteres";
          return "";
        },
        confirm_password: (value) => {
          if (!value) return "Confirmação de senha é obrigatória";
          const password = document.getElementById("password")?.value;
          if (value !== password) return "As senhas não coincidem";
          return "";
        },
      },
    },
    // Readings form - using both action selector and class for robustness
    ".readings-form, form[action*='Leituras_add_process.php'], form[action*='leituras_atualizar.php']": {
      fields: ["cod_sensor", "valor", "unidade", "data_hora", "observacoes"],
      validations: {
        cod_sensor: (value) => {
          if (!value) return "Sensor é obrigatório";
          return "";
        },
        valor: (value, form) => {
          if (!value) return "Valor é obrigatório";
          
          const numValue = parseFloat(value);
          if (isNaN(numValue)) return "Valor deve ser um número";
          
          // Get selected unit for dynamic validation
          const unidadeSelect = form.querySelector("#unidade");
          if (!unidadeSelect) return "";
          
          // Get unit value - handle both select and input elements
          let unidade = unidadeSelect.value;
          if (!unidade && unidadeSelect.tagName === 'INPUT') {
            unidade = unidadeSelect.value.trim();
          }
          
          // If no unit selected, only validate that it's a number
          if (!unidade) {
            return ""; // No unit selected, skip range validation
          }
          
          // Apply unit-specific validation
          switch (unidade) {
            case "%":
              // Humidity/percentage: 0-100
              if (numValue < 0) return "Valor não pode ser negativo para percentagem";
              if (numValue > 100) return "Valor não pode exceder 100%";
              break;
            case "°C":
              // Temperature: reasonable range
              if (numValue < -50) return "Temperatura muito baixa (mínimo -50°C)";
              if (numValue > 150) return "Temperatura muito alta (máximo 150°C)";
              break;
            case "cm":
            case "m":
              // Length/distance: non-negative
              if (numValue < 0) return "Comprimento não pode ser negativo";
              break;
            case "Lux":
              // Illuminance: non-negative
              if (numValue < 0) return "Iluminância não pode ser negativa";
              if (numValue > 100000) return "Iluminância muito alta (máximo 100.000 Lux)";
              break;
            case "µg/m3":
              // Air quality: non-negative
              if (numValue < 0) return "Concentração não pode ser negativa";
              if (numValue > 500) return "Concentração muito alta (máximo 500 µg/m3)";
              break;
            default:
              // For other units, just check if it's a valid number
              break;
          }
          
          return "";
        },
         unidade: (value, form) => {
           if (!value) return "Unidade é obrigatória";
           
           // For the edit form, unidade is a text input, so we validate against common units
           // For the add form, unidade is a select, so we trust the selected value
           const unidadeInput = form.querySelector("#unidade");
           if (unidadeInput && unidadeInput.tagName.toLowerCase() === "input") {
             // Text input validation - check if it's a reasonable unit
             const validUnits = ["°C", "%", "cm", "m", "Lux", "µg/m3", "mm", "kg", "g", "mg", "ppm", "pH", "V", "A", "W", "Hz", "dB"];
             const isValidUnit = validUnits.some(unit => value.includes(unit));
             if (!isValidUnit && value.trim() !== "") {
               return "Unidade inválida. Use unidades como °C, %, cm, m, Lux, µg/m3, etc.";
             }
           }
           
           return "";
         },
        data_hora: (value) => {
          if (!value) return "Data/Hora é obrigatória";
          
          // Basic validation - could be enhanced
          const dateValue = new Date(value);
          if (isNaN(dateValue.getTime())) return "Data/Hora inválida";
          
          return "";
        },
        observacoes: (value) => {
          // Optional field, no validation needed
          return "";
        },
      },
    },
    // Sensors form
    "form[action*='SN_add_process.php'], form[action*='SN_atualizar.php']": {
      fields: ["nome", "tipo", "descricao", "modelo", "fabricante", "localizacao", "data_instalacao"],
      validations: {
        nome: (value) => {
          if (!value.trim()) return "Nome é obrigatório";
          if (value.trim().length < 2) return "Nome deve ter pelo menos 2 caracteres";
          if (value.trim().length > 100) return "Nome deve ter no máximo 100 caracteres";
          return "";
        },
        tipo: (value) => {
          if (!value.trim()) return "Tipo é obrigatório";
          if (value.trim().length < 2) return "Tipo deve ter pelo menos 2 caracteres";
          if (value.trim().length > 50) return "Tipo deve ter no máximo 50 caracteres";
          return "";
        },
        descricao: (value) => {
          // Optional field
          if (value.trim().length > 500) return "Descrição deve ter no máximo 500 caracteres";
          return "";
        },
        modelo: (value) => {
          // Optional field
          if (value.trim().length > 100) return "Modelo deve ter no máximo 100 caracteres";
          return "";
        },
        fabricante: (value) => {
          // Optional field
          if (value.trim().length > 100) return "Fabricante deve ter no máximo 100 caracteres";
          return "";
        },
        localizacao: (value) => {
          // Optional field
          if (value.trim().length > 200) return "Localização deve ter no máximo 200 caracteres";
          return "";
        },
        data_instalacao: (value) => {
          // Optional field, but if provided should be valid
          if (!value) return ""; // Optional
          
          const dateValue = new Date(value);
          if (isNaN(dateValue.getTime())) return "Data de instalação inválida";
          
          // Optional: check if date is not in the future
          const today = new Date();
          today.setHours(0, 0, 0, 0);
          if (dateValue < today) {
            // Allow past dates (installation date can be in past)
            return "";
          }
          
          // Actually, installation date can be today or past, but not too far in future
          const maxFutureDate = new Date();
          maxFutureDate.setDate(maxFutureDate.getDate() + 30); // Allow up to 30 days in future
          
          if (dateValue > maxFutureDate) return "Data de instalação não pode ser mais de 30 dias no futuro";
          
          return "";
        },
      },
    },
  };

  // Initialize validation for each form config
  Object.entries(formConfigs).forEach(([selector, config]) => {
    const form = document.querySelector(selector);
    console.log("Checking form with selector:", selector, "Found:", form);
    if (!form) {
      console.log("No form found for selector:", selector);
      return;
    }
    
    console.log("Initializing validation for form:", form);
    console.log("Form action:", form.getAttribute('action'));
    console.log("Form classes:", form.className);

    // Error elements
    const errorElements = {};

    // Create error elements for each field
    config.fields.forEach((field) => {
      const input = document.getElementById(field);
      if (input) {
        const errorDiv = document.createElement("div");
        errorDiv.className = "form-error";
        errorDiv.id = `error-${field}`;
        input.parentNode.insertBefore(errorDiv, input.nextSibling);
        errorElements[field] = errorDiv;
      }
    });

    // Validate a single field with access to form for dynamic validation
    function validateField(field) {
      const input = document.getElementById(field);
      if (!input) return true;

      const value = input.value;
      const errorMessage = typeof config.validations[field] === 'function' 
        ? config.validations[field](value, form) 
        : config.validations[field](value);
      
      const isValid = errorMessage === "";
      
      console.log(`Validating ${field}: value="${value}", error="${errorMessage}", valid=${isValid}`);

      // Update UI
      input.classList.toggle("valid", isValid);
      input.classList.toggle("invalid", !isValid);

      if (errorElements[field]) {
        errorElements[field].textContent = errorMessage;
        errorElements[field].style.display = isValid ? "none" : "block";
      }

      return isValid;
    }

    // Validate all fields
    function validateForm() {
      let isValid = true;
      config.fields.forEach((field) => {
        if (!validateField(field)) {
          isValid = false;
        }
      });
      return isValid;
    }

    // Real-time validation on input
    config.fields.forEach((field) => {
      const input = document.getElementById(field);
      if (input) {
        input.addEventListener("input", () => validateField(field));
        input.addEventListener("blur", () => validateField(field));
        
        // Special handling for unit field in readings form - revalidate value when unit changes
        if (field === "unidade" && selector.includes("Leituras")) {
          input.addEventListener("change", () => {
            const valorInput = document.getElementById("valor");
            if (valorInput) validateField("valor");
          });
        }
      }
    });

    // Form submission
    form.addEventListener("submit", function (e) {
      console.log("Form submission attempt");
      const isValid = validateForm();
      console.log("Form validation result:", isValid);
      if (!isValid) {
        e.preventDefault();
        console.log("Form submission prevented due to validation errors");
        // Focus on first invalid field
        const firstInvalid = form.querySelector(".invalid");
        if (firstInvalid) {
          console.log("Focusing on first invalid field:", firstInvalid);
          firstInvalid.focus();
        }
      } else {
        console.log("Form submission allowed");
      }
    });
  });
});
