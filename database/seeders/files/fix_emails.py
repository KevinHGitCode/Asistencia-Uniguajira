import sys
import pandas as pd

FILE_PATH = "/home/kevin/Proyectos/laravel/Asistencia-Uniguajira/database/seeders/files/seed.xlsx"

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
        if email not in seen:
            seen[email] = 0
            new_emails.append(email)
        else:
            duplicates_found = True
            seen[email] += 1
            name, domain = email.split("@")
            unique_email = f"{name}+{seen[email]}@{domain}"
            # Asegurarse de que el nuevo correo tampoco exista
            while unique_email in seen:
                seen[email] += 1
                unique_email = f"{name}+{seen[email]}@{domain}"
            seen[unique_email] = 0
            new_emails.append(unique_email)

    if not duplicates_found:
        print("✅ No se encontraron correos duplicados. Todos los correos son únicos.")
        return

    df["Correo"] = new_emails
    df.to_excel(path, index=False)
    print(f"✅ Correos duplicados corregidos y guardados en {path}")

if __name__ == "__main__":
    fix_emails(FILE_PATH)