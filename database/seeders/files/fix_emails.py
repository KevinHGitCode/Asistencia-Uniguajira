import sys
import pandas as pd
import unicodedata

FILE_PATH = "/home/kevin/Proyectos/laravel/Asistencia-Uniguajira/database/seeders/files/seed.xlsx"

def remove_accents(input_str):
    if not isinstance(input_str, str):
        return input_str
    nfkd_form = unicodedata.normalize('NFKD', input_str)
    return "".join([c for c in nfkd_form if not unicodedata.combining(c)])

def fix_emails(path):
    df = pd.read_excel(path)

    if "Correo" not in df.columns:
        print("❌ La columna 'Correo' no existe en el archivo.")
        return

    seen = {}
    new_emails = []
    duplicates_found = False

    for email in df["Correo"]:
        if pd.isna(email):
            new_emails.append(email)
            continue
        # Normalizar el correo quitando tildes
        normalized_email = remove_accents(email.lower())
        if normalized_email not in seen:
            seen[normalized_email] = 0
            new_emails.append(email)
        else:
            duplicates_found = True
            seen[normalized_email] += 1
            name, domain = email.split("@")
            name_norm, domain_norm = remove_accents(name.lower()), remove_accents(domain.lower())
            unique_email = f"{name}+{seen[normalized_email]}@{domain}"
            # Asegurarse de que el nuevo correo tampoco exista (normalizado)
            while remove_accents(unique_email.lower()) in seen:
                seen[normalized_email] += 1
                unique_email = f"{name}+{seen[normalized_email]}@{domain}"
            seen[remove_accents(unique_email.lower())] = 0
            new_emails.append(unique_email)

    if not duplicates_found:
        print("✅ No se encontraron correos duplicados. Todos los correos son únicos.")
        return

    df["Correo"] = new_emails
    df.to_excel(path, index=False)
    print(f"✅ Correos duplicados corregidos y guardados en {path}")

if __name__ == "__main__":
    fix_emails(FILE_PATH)