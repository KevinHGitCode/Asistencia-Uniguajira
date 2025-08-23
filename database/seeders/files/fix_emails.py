import sys
import pandas as pd

# Ruta fija al archivo Excel
FILE_PATH = "/home/kevin/Proyectos/laravel/Asistencia-Uniguajira/database/seeders/files/seed.xlsx"

def fix_emails(path):
    # Leer Excel
    df = pd.read_excel(path)

    if "Correo" not in df.columns:
        print("❌ La columna 'Correo' no existe en el archivo.")
        return

    # Diccionario para contar duplicados
    seen = {}

    def make_unique(email):
        if pd.isna(email):
            return email
        if email not in seen:
            seen[email] = 0
            return email
        else:
            seen[email] += 1
            name, domain = email.split("@")
            return f"{name}+{seen[email]}@{domain}"

    # Aplicar función
    df["Correo"] = df["Correo"].apply(make_unique)

    # Guardar en el mismo archivo (sobreescribir)
    df.to_excel(path, index=False)
    print(f"✅ Correos procesados y guardados en {path}")

if __name__ == "__main__":
    fix_emails(FILE_PATH)
