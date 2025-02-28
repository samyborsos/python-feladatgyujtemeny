import sqlite3
import requests
import json
import time
from typing import Optional, Dict, Any
from requests.adapters import HTTPAdapter
from urllib3.util.retry import Retry

class SolutionGenerator:
    def __init__(self, db_path: str, ai_endpoint: str = "http://localhost:11434/api/generate"):
        self.db_path = db_path
        self.ai_endpoint = ai_endpoint
        self.model = "tinyllama"  # Back to regular tinyllama
        
        # Configure retry strategy
        retry_strategy = Retry(
            total=1,
            backoff_factor=0.5,
            status_forcelist=[429, 500, 502, 503, 504]
        )
        
        self.session = requests.Session()
        self.session.mount("http://", HTTPAdapter(max_retries=retry_strategy))

    def connect_db(self) -> sqlite3.Connection:
        """Create database connection"""
        return sqlite3.connect(self.db_path)

    def check_ollama_status(self) -> bool:
        """Check if Ollama is running and responding"""
        try:
            response = self.session.get("http://localhost:11434/api/tags", timeout=5)
            return response.status_code == 200
        except requests.exceptions.RequestException:
            return False

    def generate_solution(self, title: str, description: str) -> Optional[str]:
        """Generate solution using AI with streaming"""
        try:
            # Create function name from title
            function_name = title.lower().replace(' ', '_')
            function_name = ''.join(c for c in function_name if c.isalnum() or c == '_')

            # Prepare AI prompt
            prompt = f"""Create a Python function named '{function_name}' for this problem:
Description: {description}

STRICT Requirements:
- Function name must be: {function_name}
- No comments
- No docstrings
- No explanations
- Pure Python 3 code only
- Return the solution
- Do not include any other text or comments

Example format:
def {function_name}(nums, target):
    return result"""

            # Make streaming request to Ollama with minimal parameters
            response = self.session.post(
                self.ai_endpoint,
                json={
                    "model": self.model,
                    "prompt": prompt,
                    "stream": True,
                    "temperature": 0.7,
                    "max_tokens": 150,  # Reduced
                    "num_thread": 2,    # Minimal threading
                    "num_ctx": 128      # Minimal context
                },
                stream=True,
                timeout=60  # Increased timeout since we're using less resources
            )

            if response.status_code == 200:
                solution_parts = []
                print("Receiving solution", end="")
                for line in response.iter_lines():
                    if line:
                        try:
                            json_response = json.loads(line)
                            if 'response' in json_response:
                                solution_parts.append(json_response['response'])
                                print(".", end="", flush=True)
                        except json.JSONDecodeError:
                            continue
                print("\n")

                solution = ''.join(solution_parts)
                if solution:
                    return self.format_solution(solution)
            
            return None

        except Exception as e:
            print(f"Error generating solution: {e}")
            return None

    def format_solution(self, solution: str) -> str:
        """Format the solution with proper code blocks"""
        solution = solution.strip()
        
        # Remove docstrings and comments
        solution = '\n'.join(line for line in solution.split('\n') 
                           if not line.strip().startswith('#') 
                           and not line.strip().startswith('"""'))

        # Remove empty lines
        solution = '\n'.join(line for line in solution.split('\n') if line.strip())

        # Ensure proper code block formatting
        if not solution.startswith('```python'):
            solution = f"```python\n{solution}"
        if not solution.endswith('```'):
            solution = f"{solution}\n```"

        return solution

    def process_questions(self):
        """Process all questions without solutions"""
        conn = self.connect_db()
        cursor = conn.cursor()

        try:
            # Get questions without solutions
            cursor.execute("""
                SELECT id, title_hu, description_hu 
                FROM questions 
                WHERE solution IS NULL
                LIMIT 1  -- Process one at a time
            """)
            
            questions = cursor.fetchall()
            total_questions = len(questions)
            print(f"Processing {total_questions} question(s)")

            for index, (question_id, title, description) in enumerate(questions, 1):
                print(f"\nProcessing question {index}/{total_questions}")
                print(f"ID: {question_id}")
                print(f"Title: {title}")
                print("Generating solution...")
                
                solution = self.generate_solution(title, description)
                
                if solution:
                    cursor.execute(
                        "UPDATE questions SET solution = ? WHERE id = ?",
                        (solution, question_id)
                    )
                    conn.commit()
                    print(f"✓ Solution generated and saved")
                else:
                    print(f"✗ Failed to generate solution")

                time.sleep(5)  # Longer delay between questions

        except Exception as e:
            print(f"Error processing questions: {e}")
        finally:
            conn.close()

if __name__ == "__main__":
    print("🤖 Starting Solution Generator (Low Resource Mode)")
    generator = SolutionGenerator("database/database.sqlite")
    
    if generator.check_ollama_status():
        print("✓ Ollama is running")
        generator.process_questions()
    else:
        print("""
❌ Could not connect to Ollama!

Please make sure:
1. Ollama is installed
2. Ollama is running on localhost:11434
3. The tinyllama model is downloaded

To start Ollama:
- Windows: Run Ollama from the Windows System Tray
- Linux/Mac: Run 'ollama serve' in terminal

To download the model:
- Run: ollama pull tinyllama
""") 