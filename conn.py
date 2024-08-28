import pyodbc

# สตริงการเชื่อมต่อ
conn_str = (
    r'DRIVER={SQL Server};'  # ใช้ SQL Server Driver
    r'SERVER=(local)\SQLEXPRESS;'  # ชื่อเซิร์ฟเวอร์ที่ต้องการเชื่อมต่อ
    r'DATABASE=NewStock;'  # ระบุชื่อฐานข้อมูล
    r'Trusted_Connection=yes;'  # ใช้การเชื่อมต่อที่เชื่อถือได้ (Windows Authentication)
)

try:
    # สร้างการเชื่อมต่อ
    conn = pyodbc.connect(conn_str)
    print("เชื่อมต่อกับฐานข้อมูลสำเร็จ!")

    # สร้าง cursor เพื่อทำงานกับฐานข้อมูล
    cursor = conn.cursor()

    
    # กำหนดค่าของตัวแปร OfficerUserName
    OfficerUserName = 'username_here'  # แทนที่ 'username_here' ด้วยชื่อผู้ใช้ที่ต้องการค้นหา

    # การสร้างคำสั่ง SQL
    command_text = """
    SELECT
        Officer.OfficerNumber,
        Officer.PW,
        RTRIM(Officer.Name) AS Name
    FROM Officer
    WHERE Officer.UN = ?
    AND Officer.Status IS NULL
    """

    # การดำเนินการคำสั่ง SQL โดยใช้การป้องกัน SQL Injection ด้วยการใช้ placeholders
    cursor.execute(command_text, (OfficerUserName,))

    #  การดึงข้อมูลจากฐานข้อมูล
    results = cursor.fetchall()

    # การแสดงผลข้อมูลที่ดึงมาได้
    for row in results:
        officer_number, password, name = row
        print(f"Officer Number: {officer_number}, Password: {password}, Name: {name}")

except Exception as e:
    print(f"เกิดข้อผิดพลาด: {e}")
    

finally:
    # ปิดการเชื่อมต่อ
    conn.close()
