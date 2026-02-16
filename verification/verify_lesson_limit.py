from playwright.sync_api import sync_playwright
import time

def run(playwright):
    browser = playwright.chromium.launch(headless=True)
    context = browser.new_context()
    page = context.new_page()

    try:
        # 1. Login
        print("Logging in...")
        page.goto("http://localhost:8080")
        if page.is_visible('input[name="user"]'):
            page.fill('input[name="user"]', "admin_updated") # Or admin if reset
            page.fill('input[name="pass"]', "newpass123")
            page.click('button[name="login"]')
            # If fail, try default
            if page.is_visible('button[name="login"]'):
                 print("Login failed, trying default...")
                 page.fill('input[name="user"]', "admin")
                 page.fill('input[name="pass"]', "admin123")
                 page.click('button[name="login"]')
            page.wait_for_selector('text=Boshqaruv Paneli')

        # 2. Add Group with 10 lessons
        print("Adding Group with 10 lessons...")
        page.goto("http://localhost:8080/?p=settings")
        group_name = "LimitGroup_" + str(int(time.time()))
        page.fill('input[name="name"]', group_name)
        page.fill('input[name="price"]', "120000")
        page.fill('input[name="lessons"]', "10")
        page.click('button:has-text("Qo\'shish")')
        page.wait_for_load_state("networkidle")

        # Verify in list
        if page.locator(f'td:has-text("{group_name}")').count() > 0:
            print("Group added.")
            # Verify lessons count in table
            row = page.locator(f'tr:has-text("{group_name}")')
            if "10 ta" in row.inner_text():
                print("Group shows 10 lessons correctly.")
            else:
                print("FAIL: Group does not show 10 lessons.")
        else:
            print("FAIL: Group not found.")

        # 3. Add Student
        print("Adding Student...")
        page.goto("http://localhost:8080/?p=dashboard")
        student_name = "LimitStudent_" + str(int(time.time()))
        page.fill('input[name="st_name"]', student_name)
        page.select_option('select[name="st_school"]', index=1)

        # Select group (reload page first to ensure new group is in JS list?)
        # Actually dashboard fetches groups on load.
        page.reload()
        page.fill('input[name="st_name"]', student_name)
        page.select_option('select[name="st_school"]', index=1)

        # Select the new group
        # We need to find the option with the group name
        # It's in select[name="st_group[]"]
        select = page.locator('select[name="st_group[]"]').first
        select.select_option(label=group_name)

        page.click('button[name="add_student"]')
        page.wait_for_load_state("networkidle")

        # 4. Verify Student Limit in Edit Page
        print("Verifying Student Limit...")
        page.goto("http://localhost:8080/?p=students")
        # Find edit link for this student
        # The edit link is the second button in the actions column (pencil icon)
        # We find the row, then the link
        row = page.locator(f'tr:has-text("{student_name}")')
        edit_link = row.locator('a[href*="edit_student"]').first
        edit_url = edit_link.get_attribute("href")
        print(f"Edit URL: {edit_url}")

        page.goto("http://localhost:8080/" + edit_url)

        # Check input value
        limit_input = page.locator('input[name="st_lessons_limit"]')
        val = limit_input.input_value()
        print(f"Current Limit: {val}")

        if val == "10":
            print("SUCCESS: Student inherited 10 lessons limit.")
        else:
            print(f"FAIL: Expected 10, got {val}")

        # 5. Change Limit to 5
        print("Changing limit to 5...")
        limit_input.fill("5")
        page.click('button:has-text("Saqlash")')
        page.wait_for_load_state("networkidle")

        # Verify change
        page.goto("http://localhost:8080/" + edit_url)
        val_new = page.locator('input[name="st_lessons_limit"]').input_value()
        if val_new == "5":
             print("SUCCESS: Limit updated to 5.")
        else:
             print(f"FAIL: Expected 5, got {val_new}")

        # 6. Mark Attendance (Present)
        # We need student ID from URL
        sid = edit_url.split("id=")[1]
        print(f"Student ID: {sid}")

        # Mark present for today
        # We can simulate the POST request or use the Attendance page
        # Using Attendance page might be complex due to filtering.
        # Let's just simulate POST to save_att
        # index.php logic: if isset($_POST['save_att']), foreach $_POST['att'] as $sid => $status

        print("Marking attendance via POST...")
        # We can construct a fetch call in the page context
        page.evaluate(f"""
            const formData = new FormData();
            formData.append('save_att', '1');
            formData.append('att[{sid}]', 'Present');
            fetch('index.php?p=attendance', {{
                method: 'POST',
                body: formData
            }});
        """)
        time.sleep(2) # Wait for DB update

        # 7. Check Reports
        print("Checking Reports...")
        page.goto("http://localhost:8080/?p=reports")

        # Find the row for this student
        # Logic: Fee is 120000. Limit is 5.
        # Expected Earned = (120000 / 5) * 1 = 24000.

        # We need to find the text "24,000" in the row for this student
        # Row format: Student Name ... Earned ...

        # The table is in a scrollable div
        # Let's just search for text
        if page.locator(f'text=24,000').count() > 0:
             print("SUCCESS: Earned amount is 24,000 (Correctly calculated based on 5 lessons).")
        else:
             print("FAIL: Could not find 24,000 in reports.")
             page.screenshot(path="verification/reports_fail.png")

    except Exception as e:
        print(f"Error: {e}")
        import traceback
        traceback.print_exc()
        page.screenshot(path="verification/error_limit.png")
    finally:
        browser.close()

with sync_playwright() as playwright:
    run(playwright)
