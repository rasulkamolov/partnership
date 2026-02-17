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
            page.fill('input[name="user"]', "admin_updated")
            page.fill('input[name="pass"]', "newpass123")
            page.click('button[name="login"]')
            page.wait_for_selector('text=Boshqaruv Paneli', timeout=5000)

        # 2. Add New Student with MULTIPLE Groups
        print("Enrolling student with 2 groups...")
        page.goto("http://localhost:8080/?p=dashboard")

        # Check if School form is visible
        if page.is_visible('input[name="sch_name"]'):
            page.fill('input[name="sch_name"]', "Test School 4")
            page.fill('input[name="sch_user"]', "school4_" + str(time.time()))
            page.fill('input[name="sch_pass"]', "pass4")
            page.fill('input[name="sch_contact"]', "444")
            page.click('button[name="add_school"]')
            page.wait_for_load_state("networkidle")
        else:
            print("School form not visible, skipping addition.")

        student_name = "MultiGroup Student " + str(int(time.time()))

        # Check Groups
        # Need to ensure global groups exist
        # We can just go to Settings and add if needed
        # But let's assume previous test added some.
        # Check dropdown count
        opts = page.locator('select[name="st_group[]"]').first.locator('option')
        if opts.count() <= 1:
             print("No global groups found. Creating some...")
             page.goto("http://localhost:8080/?p=settings")
             page.fill('input[name="name"]', "Group M1")
             page.fill('input[name="price"]', "100")
             page.click('button:has-text("Qo\'shish")')
             page.wait_for_timeout(500)
             page.fill('input[name="name"]', "Group M2")
             page.fill('input[name="price"]', "200")
             page.click('button:has-text("Qo\'shish")')
             page.wait_for_load_state("networkidle")
             page.goto("http://localhost:8080/?p=dashboard")

        page.fill('input[name="st_name"]', student_name)
        # Select school (index 1)
        page.select_option('select[name="st_school"]', index=1)

        # Select first group
        page.locator('select[name="st_group[]"]').first.select_option(index=1)

        # Add second group row
        print("Adding second group row...")
        page.click('button:has-text("Yana guruh qo\'shish")')

        # Select second group in second row
        # Locator for second select
        page.locator('select[name="st_group[]"]').nth(1).select_option(index=2) # Assuming at least 2 groups

        # Submit
        print("Submitting enrollment...")
        page.click('button[name="add_student"]')
        page.wait_for_load_state("networkidle")

        # Verify both enrollments
        print("Verifying enrollments...")
        page.goto("http://localhost:8080/?p=students")
        count = page.locator(f'tr:has-text("{student_name}")').count()
        if count == 2:
             print("SUCCESS: Student added with 2 groups initially.")
        else:
             print(f"FAIL: Expected 2 rows for student, found {count}.")
             # Debug output
             print(page.inner_text('tbody'))

        # 3. Add 3rd Group to Existing Student
        print("Adding 3rd group to existing student...")
        # Click "Add Group" (plus circle) on first row of this student
        # Locate row
        row = page.locator(f'tr:has-text("{student_name}")').first
        if row.count() > 0:
            # Click plus button inside row
            row.locator('a[href*="add_enrollment"]').click()

            page.wait_for_selector("text=O'quvchiga Guruh Qo'shish")

            # Add a group
            page.select_option('select[name="st_group[]"]', index=1)
            page.click('button:has-text("Saqlash")')
            page.wait_for_load_state("networkidle")

            # Verify 3 enrollments
            page.goto("http://localhost:8080/?p=students")
            count_final = page.locator(f'tr:has-text("{student_name}")').count()
            if count_final == 3:
                 print("SUCCESS: Added 3rd group to existing student.")
            else:
                 print(f"FAIL: Expected 3 rows, found {count_final}.")
        else:
            print("Row not found for adding 3rd group.")

        # Screenshot List
        page.screenshot(path="verification/multi_group_list.png")

    except Exception as e:
        print(f"Error: {e}")
        import traceback
        traceback.print_exc()
        page.screenshot(path="verification/error_multi.png")
    finally:
        browser.close()

with sync_playwright() as playwright:
    run(playwright)
