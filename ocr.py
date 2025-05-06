import io
import sys
import json
import logging
from paddleocr import PaddleOCR

# 禁用DEBUG日志
logging.disable(logging.DEBUG)
# 设置标准输出的编码为utf-8
sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8')

# 初始化PaddleOCR，启用方向分类器，设置语言为中文
ocr = PaddleOCR(use_angle_cls=True, lang='ch')

# 读取图片路径（从命令行参数获取）
image_path = sys.argv[1]  # 这里修正了语法错误

# 执行OCR识别
result = ocr.ocr(image_path, cls=True)

# 解析OCR结果，只保留企业名称（假设企业名称位于每行的第一个单元格）
formatted_result = []
for line in result[0]:  # result[0] 通常包含文本行的信息
    # 假设每行的第一个元素是文本框坐标，第二个元素是文本和置信度的列表
    text_and_confidence = line[1]
    # 提取文本和置信度
    formatted_result.append({
        "text": text_and_confidence[0],
        "confidence": float(text_and_confidence[1]),
        "position": line[0]  # 文本框坐标
    })

# 打印格式化后的JSON结果
print(json.dumps(formatted_result, ensure_ascii=False, indent=2))
