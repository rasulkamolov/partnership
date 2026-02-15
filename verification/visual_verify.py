from playwright.sync_api import sync_playwright

def run(playwright):
    browser = playwright.chromium.launch(headless=True)
    context = browser.new_context()
    page = context.new_page()

    try:
        # 1. Login as Admin
        print("Logging in...")
        page.goto("http://localhost:8080")
        if page.is_visible('input[name="user"]'):
            page.fill('input[name="user"]', "admin") # Try default admin first (if ID 1 was reset to admin)
            # Wait, I updated ID 1 to be admin, but username might still be 'admin_updated' if verify_multi_group.py changed it.
            # verify_multi_group.py changed it to 'admin_updated'.
            # But wait, if I use the SAME database, it persists.
            # Let's try 'admin_updated' first.
            page.fill('input[name="user"]', "admin_updated")
            page.fill('input[name="pass"]', "newpass123")
            page.click('button[name="login"]')
            try:
                page.wait_for_selector('text=Boshqaruv Paneli', timeout=2000)
            except:
                print("Login with admin_updated failed, trying default admin...")
                page.fill('input[name="user"]', "admin")
                page.fill('input[name="pass"]', "admin123")
                page.click('button[name="login"]')
                page.wait_for_selector('text=Boshqaruv Paneli')

        # 2. Dashboard Screenshot (showing Multi-Group form)
        print("Capturing Dashboard...")
        page.goto("http://localhost:8080/?p=dashboard")
        # Scroll to enrollment form
        page.locator('text=O\'quvchi Qo\'shish').scroll_into_view_if_needed()
        page.screenshot(path="verification/dashboard_enrollment.png")

        # 3. Students List Screenshot (showing multi-enrollment)
        print("Capturing Students List...")
        page.goto("http://localhost:8080/?p=students")
        page.screenshot(path="verification/students_list.png")

        # 4. Add Enrollment Page Screenshot
        print("Capturing Add Enrollment Page...")
        # Find a student to add enrollment to
        # Navigate to first 'plus' icon
        plus_btn = page.locator('a[href*="add_enrollment"]').first
        if plus_btn.count() > 0:
            plus_btn.click()
            page.wait_for_selector("text=O'quvchiga Guruh Qo'shish")
            page.screenshot(path="verification/add_enrollment_page.png")
        else:
            print("No students found to add enrollment to.")

    except Exception as e:
        print(f"Error: {e}")
    finally:
        browser.close()

with sync_playwright() as playwright:
    run(playwright)
