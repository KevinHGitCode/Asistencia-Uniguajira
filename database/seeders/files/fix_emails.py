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

    for email in df["Correo"]:
        if pd.isna(email):
            new_emails.append(email)
            continue
        if email not in seen:
            seen[email] = 0
            new_emails.append(email)
        else:
            seen[email] += 1
            name, domain = email.split("@")
            new_emails.append(f"{name}+{seen[email]}@{domain}")

    df["Correo"] = new_emails
    df.to_excel(path, index=False)
    print(f"✅ Correos procesados y guardados en {path}")

if __name__ == "__main__":
    fix_emails(FILE_PATH)